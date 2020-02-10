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

namespace NavBundle\EntityPersister;

use NavBundle\EntityManager\EntityManagerInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class EntityPersister implements EntityPersisterInterface
{
    private $em;
    private $className;

    public function __construct(EntityManagerInterface $em, string $className)
    {
        $this->em = $em;
        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $criteria): ?object
    {
        foreach ($this->loadAll($criteria, 1) as $object) {
            return $object;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function loadAll(array $criteria = [], $limit = null, $offset = null): \Iterator
    {
        $builder = $this->em->createRequestBuilder($this->className);

        foreach ($criteria as $key => $value) {
            $builder->andWhere($key, $value);
        }

        if (null !== $limit) {
            $builder->setSize($limit);
        }

        if (null !== $offset) {
            $builder->setBookmarkKey($offset);
        }

        return $builder->getResult();
    }
}
