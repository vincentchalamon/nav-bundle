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
interface RepositoryInterface
{
    /**
     * Get the repository entity class name.
     *
     * @return string the entity class name
     */
    public function getClass();

    /**
     * Finds an entity by its primary key / identifier.
     *
     * @param string $no the primary key / identifier
     *
     * @return object|null the entity instance or NULL if the entity cannot be found
     */
    public function find(string $no);

    /**
     * Finds all entities in the repository.
     *
     * @return iterable the entities
     */
    public function findAll();

    /**
     * Finds entities by a set of criteria.
     *
     * @param array $criteria the criteria
     * @param int   $size     the size
     *
     * @return iterable the entities
     */
    public function findBy(array $criteria = [], int $size = 0);

    /**
     * Finds 1 entity by a set of criteria.
     *
     * @param array $criteria the criteria
     *
     * @return object|null the entity
     */
    public function findOneBy(array $criteria = []);
}
