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

use matejsvajger\NTLMSoap\Client;
use NavBundle\ClassMetadata\ClassMetadataInterface;
use NavBundle\Repository\RepositoryInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
interface ManagerInterface
{
    /**
     * Check if manager handle an entity class.
     *
     * @param string $class the entity class
     *
     * @return bool TRUE if manager handle the entity class, otherwise FALSE
     */
    public function hasClass(string $class): bool;

    /**
     * Get the related ClassMetadata.
     *
     * @return ClassMetadataInterface the ClassMetadata object
     */
    public function getClassMetadata(): ClassMetadataInterface;

    /**
     * Get the Client for a class.
     *
     * @return Client the Client object
     */
    public function getClient(string $class): Client;

    /**
     * Get the entity repository.
     *
     * @param string $class the entity class
     *
     * @return RepositoryInterface the entity repository
     */
    public function getRepository(string $class): RepositoryInterface;

    /**
     * Refresh an entity.
     *
     * @param object $entity the entity
     */
    public function refresh(object &$entity): void;

    /**
     * Finds an entity by its primary key / identifier.
     *
     * @param string $class the entity class
     * @param string $id    the primary key / identifier
     *
     * @return object|null the entity instance or NULL if the entity cannot be found
     */
    public function find(string $class, string $id);

    /**
     * Finds all entities in the repository.
     *
     * @param string $class the entity class
     *
     * @return array the entities
     */
    public function findAll(string $class);

    /**
     * Finds entities by a set of criteria.
     *
     * @param string $class    the entity class
     * @param array  $criteria the criteria
     * @param int    $size     the size
     *
     * @return array the entities
     */
    public function findBy(string $class, array $criteria = [], int $size = 0);
}
