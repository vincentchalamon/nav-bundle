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

namespace NavBundle\Collection;

use Doctrine\Common\Collections\AbstractLazyCollection;
use Doctrine\Common\Collections\Collection;
use NavBundle\RegistryInterface;
use NavBundle\Util\ClassUtils;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class LazyCollection extends AbstractLazyCollection
{
    protected $registry;
    protected $associationName;
    protected $owner;

    public function __construct(RegistryInterface $registry, Collection $collection, $associationName, $owner)
    {
        $this->registry = $registry;
        $this->collection = $collection;
        $this->associationName = $associationName;
        $this->owner = $owner;
    }

    /**
     * {@inheritdoc}
     */
    protected function doInitialize(): void
    {
        $loadedObjects = $this->collection->toArray();
        $this->collection->clear();

        $ownerClassName = ClassUtils::getRealClass($this->owner);
        $ownerClassMetadata = $this->registry->getManagerForClass($ownerClassName)->getClassMetadata($ownerClassName);
        $targetClass = $ownerClassMetadata->getAssociationTargetClass($this->associationName);

        $newObjects = $this->registry->getManagerForClass($targetClass)->getRepository($targetClass)->findBy([
            $ownerClassMetadata->getAssociationMappedByTargetField($this->associationName) => $ownerClassMetadata->getIdentifierValue($this->owner),
        ]);

        $newObjectsByOid = [];
        foreach ($newObjects as $object) {
            $newObjectsByOid[spl_object_hash($object)] = $object;
        }
        $loadedObjectsByOid = array_combine(array_map('spl_object_hash', $loadedObjects), $loadedObjects);
        $newObjectsThatWereNotLoaded = array_diff_key($newObjectsByOid, $loadedObjectsByOid);
        if ($newObjectsThatWereNotLoaded) {
            array_walk($newObjectsThatWereNotLoaded, [$this->collection, 'add']);
        }

        $this->initialized = true;
    }
}
