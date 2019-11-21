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

namespace NavBundle\Manager;

use NavBundle\Repository\NavRepositoryInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
interface NavManagerInterface
{
    /**
     * Check if manager handle an entity class.
     *
     * @param string $class The entity class.
     *
     * @return bool TRUE if manager handle the entity class, otherwise FALSE.
     */
    public function hasClass(string $class): bool;

    /**
     * Get the entity repository.
     *
     * @param string $class The entity class.
     *
     * @return NavRepositoryInterface The entity repository.
     */
    public function getRepository(string $class): NavRepositoryInterface;

    /**
     * Finds an entity by its primary key / identifier.
     *
     * @param string $namespace The NAV namespace.
     * @param string $no        The primary key / identifier.
     *
     * @return object|null The entity instance or NULL if the entity cannot be found.
     */
    public function find(string $namespace, string $no);

    /**
     * Finds all entities in the repository.
     *
     * @param string $namespace The NAV namespace.
     *
     * @return array The entities.
     */
    public function findAll(string $namespace);

    /**
     * Finds entities by a set of criteria.
     *
     * @param string $namespace The NAV namespace.
     * @param array $criteria   The criteria.
     * @param int   $size       The size.
     *
     * @return array The entities.
     */
    public function findBy(string $namespace, array $criteria = [], int $size = 0);
}
