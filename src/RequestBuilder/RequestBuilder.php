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

namespace NavBundle\RequestBuilder;

use NavBundle\EntityManager\EntityManagerInterface;
use NavBundle\Event\PostLoadEvent;
use NavBundle\Exception\FieldNotFoundException;
use NavBundle\Hydrator\CountHydrator;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class RequestBuilder implements RequestBuilderInterface
{
    private $em;
    private $className;
    private $filters = [];
    private $offset;
    private $limit;

    public function __construct(EntityManagerInterface $em, string $className)
    {
        $this->em = $em;
        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function where($field, $predicate)
    {
        $this->filters = [];

        return $this->andWhere($field, $predicate);
    }

    /**
     * {@inheritdoc}
     */
    public function andWhere($field, $predicate)
    {
        $this->filters[$field] = $predicate;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setFirstResult($firstResult)
    {
        $this->offset = $firstResult;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstResult()
    {
        return $this->offset;
    }

    /**
     * {@inheritdoc}
     */
    public function setMaxResults($maxResults)
    {
        $this->limit = $maxResults;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxResults()
    {
        return $this->limit;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \SoapFault
     */
    public function loadById($identifier): ?object
    {
        try {
            $response = $this->em->getConnection($this->className)->Read([
                'No' => $identifier,
            ]);
        } catch (\SoapFault $fault) {
            $this->em->getLogger()->critical($fault->getMessage());

            throw $fault;
        }

        if (empty($response)) {
            return null;
        }

        $object = $this->em->getHydrator()->hydrateAll($response, $this->em->getClassMetadata($this->className));
        $this->em->getEventManager()->dispatch(new PostLoadEvent($object, $this->em));

        return $object;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \SoapFault
     */
    public function getOneOrNullResult(string $hydrator = null): ?object
    {
        $this->setMaxResults(1);

        foreach ($this->getResult($hydrator) as $object) {
            $this->em->getEventManager()->dispatch(new PostLoadEvent($object, $this->em));

            return $object;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \SoapFault
     */
    public function getResult(string $hydrator = null): iterable
    {
        try {
            $response = $this->em->getConnection($this->className)->ReadMultiple($this->computeFilters());
        } catch (\SoapFault $fault) {
            $this->em->getLogger()->critical($fault->getMessage());

            throw $fault;
        }

        if (empty($response)) {
            return yield from [];
        }

        $objects = $this->em->getHydrator($hydrator)->hydrateAll($response, $this->em->getClassMetadata($this->className));
        foreach ($objects as $object) {
            $this->em->getEventManager()->dispatch(new PostLoadEvent($object, $this->em));
            yield $object;
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \SoapFault
     */
    public function count()
    {
        try {
            $response = $this->em->getConnection($this->className)->ReadMultiple($this->computeFilters());
        } catch (\SoapFault $fault) {
            $this->em->getLogger()->critical($fault->getMessage());

            throw $fault;
        }

        return $this->em->getHydrator(CountHydrator::class)->hydrateAll($response, $this->em->getClassMetadata($this->className));
    }

    private function computeFilters(): array
    {
        $criteria = [
            'filter' => [],
            'setSize' => $this->limit,
        ];

        if (null !== $this->offset) {
            $criteria['bookmarkKey'] = $this->offset;
        }

        $classMetadata = $this->em->getClassMetadata($this->className);
        foreach ($this->filters as $field => $value) {
            if ($classMetadata->hasField($field)) {
                $field = $classMetadata->getFieldColumnName($field);
            // TODO: Transform value if necessary.
            } elseif ($classMetadata->hasAssociation($field)) {
                // TODO: Transform value if necessary.
                if ($classMetadata->isSingleValuedAssociation($field)) {
                    $field = $classMetadata->getSingleValuedAssociationColumnName($field);
                } else {
                    $field = $classMetadata->getAssociationMappedByTargetField($field);
                }
            } else {
                throw new FieldNotFoundException("Field name expected, '$field' is not a field nor an association.");
            }

            $criteria['filter'][] = [
                'Field' => $field,
                'Criteria' => $value,
            ];
        }

        return $criteria;
    }
}
