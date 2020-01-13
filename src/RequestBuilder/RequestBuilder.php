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

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class RequestBuilder implements RequestBuilderInterface
{
    private $em;
    private $className;
    private $filters = [];
    private $offset;
    private $limit = 0;

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

        if (!$response) {
            return null;
        }

        $object = $this->em->getHydrator()->hydrateAll($response, $this->em->getClassMetadata($this->className));
        $this->em->getUnitOfWork()->addToIdentityMap($object);
        $this->em->getEventManager()->dispatch(new PostLoadEvent($object, $this->em));

        return $object;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \SoapFault
     */
    public function getOneOrNullResult(): ?object
    {
        $this->setMaxResults(1);

        foreach ($this->getResult() as $object) {
            $this->em->getUnitOfWork()->addToIdentityMap($object);
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
    public function getResult(): iterable
    {
        try {
            $response = $this->em->getConnection($this->className)->ReadMultiple($this->computeFilters());
        } catch (\SoapFault $fault) {
            $this->em->getLogger()->critical($fault->getMessage());

            throw $fault;
        }

        if (!$response) {
            return yield from [];
        }

        $objects = $this->em->getHydrator()->hydrateAll($response, $this->em->getClassMetadata($this->className));
        $uow = $this->em->getUnitOfWork();
        foreach ($objects as $object) {
            $uow->addToIdentityMap($object);
            $this->em->getEventManager()->dispatch(new PostLoadEvent($object, $this->em));
            yield $object;
        }
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
            $criteria['filter'][] = [
                'Field' => $classMetadata->getFieldColumnName($field),
                'Criteria' => $value,
            ];
        }

        return $criteria;
    }
}
