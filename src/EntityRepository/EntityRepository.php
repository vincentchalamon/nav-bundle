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

namespace NavBundle\EntityRepository;

use Doctrine\Persistence\ObjectRepository;
use NavBundle\EntityManager\EntityManagerInterface;
use NavBundle\EntityPersister\EntityPersisterInterface;
use NavBundle\RequestBuilder\RequestBuilderInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class EntityRepository implements ObjectRepository
{
    protected $em;
    protected $className;

    public function __construct(EntityManagerInterface $em, string $className)
    {
        $this->em = $em;
        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function find($id): ?object
    {
        return $this->em->find($this->className, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(): iterable
    {
        return $this->findBy([]);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): iterable
    {
        return $this->getEntityPersister()->loadAll($criteria, $limit, $offset);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria, array $orderBy = null): ?object
    {
        return $this->getEntityPersister()->load($criteria, $orderBy);
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    protected function createRequestBuilder(): RequestBuilderInterface
    {
        return $this->em->createRequestBuilder($this->className);
    }

    protected function getEntityPersister(): EntityPersisterInterface
    {
        return $this->em->getUnitOfWork()->getEntityPersister($this->className);
    }
}
