<?php

/*
 * This file is part of the NavBundle.
 *
 * (c) Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace NavBundle;

use NavBundle\ClassMetadata\ClassMetadata;
use NavBundle\EntityManager\EntityManagerInterface;
use NavBundle\EntityPersister\EntityPersister;
use NavBundle\EntityPersister\EntityPersisterInterface;
use NavBundle\Event\PostPersistEvent;
use NavBundle\Event\PostRemoveEvent;
use NavBundle\Event\PostUpdateEvent;
use NavBundle\Event\PrePersistEvent;
use NavBundle\Event\PreRemoveEvent;
use NavBundle\Event\PreUpdateEvent;
use NavBundle\Exception\ObjectNotManagedException;
use NavBundle\Serializer\NavDecoder;
use NavBundle\Util\ClassUtils;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class UnitOfWork
{
    private $em;
    private $normalizer;

    private $originalEntityData = [];
    private $identityMap = [];
    private $entityChangeSets = [];
    private $entitiesScheduledForInsertion = [];
    private $entitiesScheduledForDeletion = [];
    private $entitiesScheduledForUpdate = [];

    public function __construct(EntityManagerInterface $em, NormalizerInterface $normalizer)
    {
        $this->em = $em;
        $this->normalizer = $normalizer;
    }

    /**
     * Tries to get an entity by its identifier hash. If no entity is found for the given hash, NULL is returned.
     *
     * @param string $sortedId  the entity identifier
     * @param string $className the entity name
     *
     * @return object|null the found entity or NULL
     */
    public function tryGetById(string $sortedId, string $className): ?object
    {
        return $this->identityMap[$className][$sortedId] ?? null;
    }

    /**
     * Gets the EntityPersister for an entity.
     *
     * @param string $className the name of the entity
     */
    public function getEntityPersister(string $className): EntityPersisterInterface
    {
        return new EntityPersister($this->em, $className);
    }

    /**
     * Persists an entity as part of the current unit of work.
     *
     * @param object $object the entity to persist
     */
    public function persist(object $object): void
    {
        if (!$this->isInIdentityMap($object)) {
            $this->entitiesScheduledForInsertion[spl_object_hash($object)] = $object;
        }
    }

    /**
     * Deletes an entity as part of the current unit of work.
     *
     * @param object $object the entity to remove
     */
    public function remove(object $object): void
    {
        if (!$this->isInIdentityMap($object)) {
            throw new ObjectNotManagedException();
        }

        $this->entitiesScheduledForDeletion[spl_object_hash($object)] = $object;
    }

    /**
     * Clears the UnitOfWork.
     *
     * @param string|null $objectName if given, only entities of this type will get detached
     */
    public function clear(?string $objectName = null): void
    {
        if (empty($objectName)) {
            $this->identityMap = [];

            return;
        }

        unset($this->identityMap[$objectName]);
    }

    /**
     * Refreshes the given entity from the database, overwriting any local, unpersisted changes.
     *
     * @param object $object the entity to refresh
     */
    public function refresh(object $object): void
    {
        $className = ClassUtils::getRealClass($object);
        $this->addToIdentityMap(
            $this->em->createRequestBuilder($className)->loadById(
                $this->em->getClassMetadata($className)->getIdentifierValue($object)
            )
        );
    }

    /**
     * Commits the UnitOfWork, executing all operations that have been postponed up to this point.
     *
     * @param object|array|null $object the entity or an array of entities to flush
     *
     * @throws ExceptionInterface
     * @throws \SoapFault
     */
    public function commit($object = null): void
    {
        $this->computeChangeSets($object);
        $orgOid = $object ? spl_object_hash($object) : null;

        foreach ($this->entitiesScheduledForInsertion as $oid => $obj) {
            if (null !== $orgOid && $orgOid !== $oid) {
                continue;
            }

            $this->em->getEventManager()->dispatch(new PrePersistEvent($obj, $this->em));

            $className = ClassUtils::getRealClass($obj);
            $classMetadata = $this->em->getClassMetadata($className);

            try {
                $response = $this->em->getConnection($className)->Create([
                    $classMetadata->getNamespace() => $this->normalizer->normalize($obj, NavDecoder::FORMAT),
                ]);
            } catch (\SoapFault $fault) {
                $this->em->getLogger()->critical($fault->getMessage());

                throw $fault;
            }

            $this->em->getHydrator()->hydrateAll($response, $classMetadata, [
                'object_to_populate' => $obj,
            ]);
            $this->addToIdentityMap($obj);
            unset($this->entitiesScheduledForInsertion[$oid]);

            $this->em->getEventManager()->dispatch(new PostPersistEvent($obj, $this->em));
        }

        foreach ($this->entitiesScheduledForUpdate as $oid => $obj) {
            if (null !== $orgOid && $orgOid !== $oid) {
                continue;
            }

            $changeSet = $this->entityChangeSets[$oid];
            $this->em->getEventManager()->dispatch(new PreUpdateEvent($obj, $this->em, $changeSet));

            $className = ClassUtils::getRealClass($obj);
            $classMetadata = $this->em->getClassMetadata($className);

            try {
                $response = $this->em->getConnection($className)->Update([
                    $classMetadata->getNamespace() => $this->normalizer->normalize($obj, NavDecoder::FORMAT, [
                        'properties' => array_merge(array_keys($changeSet), ['key']),
                    ]),
                ]);
            } catch (\SoapFault $fault) {
                $this->em->getLogger()->critical($fault->getMessage());

                throw $fault;
            }

            $this->em->getHydrator()->hydrateAll($response, $classMetadata, [
                'object_to_populate' => $obj,
            ]);
            $this->addToIdentityMap($obj);
            unset(
                $this->entityChangeSets[$oid],
                $this->entitiesScheduledForUpdate[$oid]
            );

            $this->em->getEventManager()->dispatch(new PostUpdateEvent($obj, $this->em));
        }

        foreach ($this->entitiesScheduledForDeletion as $oid => $obj) {
            if (null !== $orgOid && $orgOid !== $oid) {
                continue;
            }

            $this->em->getEventManager()->dispatch(new PreRemoveEvent($obj, $this->em));

            $className = ClassUtils::getRealClass($obj);
            $classMetadata = $this->em->getClassMetadata($className);

            try {
                $this->em->getConnection($className)->Delete([
                    'Key' => $classMetadata->getKeyValue($obj),
                ]);
            } catch (\SoapFault $fault) {
                $this->em->getLogger()->critical($fault->getMessage());

                throw $fault;
            }

            unset(
                $this->identityMap[$className][$classMetadata->getIdentifierValue($obj)],
                $this->entitiesScheduledForDeletion[$oid],
                $this->originalEntityData[$oid]
            );

            $this->em->getEventManager()->dispatch(new PostRemoveEvent($obj, $this->em));
        }
    }

    /**
     * Checks whether an entity is scheduled for insertion.
     *
     * @param object $object the entity to check
     */
    public function isScheduledForInsert(object $object): bool
    {
        return isset($this->entitiesScheduledForInsertion[spl_object_hash($object)]);
    }

    /**
     * Checks whether an entity is registered as removed/deleted.
     *
     * @param object $object the entity to check
     */
    public function isScheduledForDelete(object $object): bool
    {
        return isset($this->entitiesScheduledForDeletion[spl_object_hash($object)]);
    }

    /**
     * Checks whether an entity is registered in the identity map of this UnitOfWork.
     *
     * @param object $object the entity to check
     */
    public function isInIdentityMap(object $object): bool
    {
        $className = ClassUtils::getRealClass($object);

        return !empty($this->identityMap[$className][$this->em->getClassMetadata($className)->getIdentifierValue($object)]);
    }

    /**
     * Adds an entity in the identity map of this UnitOfWork.
     *
     * @param object $object the entity to check
     */
    public function addToIdentityMap($object): void
    {
        $className = ClassUtils::getRealClass($object);
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $this->em->getClassMetadata($className);

        $this->identityMap[$className][$classMetadata->getIdentifierValue($object)] = $object;

        $oid = spl_object_hash($object);
        $this->originalEntityData[$oid] = [];
        foreach ($classMetadata->reflFields as $fieldName => $refProp) {
            $this->originalEntityData[$oid][$fieldName] = $refProp->getValue($object);
        }
    }

    private function computeChangeSets($object = null): void
    {
        if (null !== $object) {
            $this->computeSingleEntityChangeSets($object);

            return;
        }

        foreach ($this->identityMap as $className => $objects) {
            foreach ($objects as $id => $object) {
                $this->computeSingleEntityChangeSets($object);
            }
        }
    }

    private function computeSingleEntityChangeSets($object): void
    {
        $oid = spl_object_hash($object);
        $className = ClassUtils::getRealClass($object);
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $this->em->getClassMetadata($className);

        $actualData = [];
        foreach ($classMetadata->reflFields as $fieldName => $refProp) {
            if (!$classMetadata->isIdentifier($fieldName) && !$classMetadata->isCollectionValuedAssociation($fieldName)) {
                $actualData[$fieldName] = $refProp->getValue($object);
            }
        }

        $originalData = $this->originalEntityData[$oid];
        $changeSet = [];
        foreach ($actualData as $propName => $actualValue) {
            $orgValue = $originalData[$propName] ?? null;
            if ($orgValue !== $actualValue) {
                $changeSet[$propName] = [$orgValue, $actualValue];
            }
        }

        if ($changeSet) {
            $this->entityChangeSets[$oid] = $changeSet;
            $this->entitiesScheduledForUpdate[$oid] = $object;
        }
    }
}
