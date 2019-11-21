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

namespace NavBundle\Repository;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
interface NavRepositoryInterface
{
    /**
     * Finds an entity by its primary key / identifier.
     *
     * @param string $no The primary key / identifier.
     *
     * @return object|null The entity instance or NULL if the entity cannot be found.
     */
    public function find(string $no);

    /**
     * Finds all entities in the repository.
     *
     * @return array The entities.
     */
    public function findAll();

    /**
     * Finds entities by a set of criteria.
     *
     * @param array $criteria The criteria.
     * @param int   $size     The size.
     *
     * @return array The entities.
     */
    public function findBy(array $criteria = [], int $size = 0);
}
