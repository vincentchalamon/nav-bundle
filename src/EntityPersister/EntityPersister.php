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
    public function loadAll(array $criteria = [], int $limit = null, string $offset = null): \Traversable
    {
        $requestBuilder = $this->em->createRequestBuilder($this->className);

        foreach ($criteria as $key => $value) {
            $requestBuilder->andWhere($key, $value);
        }

        if (null !== $limit) {
            $requestBuilder->setSize($limit);
        }

        if (null !== $offset) {
            $requestBuilder->setBookmarkKey($offset);
        }

        return $requestBuilder->getResult();
    }
}
