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

use NavBundle\ClassMetadata\ClassMetadataInterface;
use NavBundle\ClassMetadata\Driver\ClassMetadataDriverInterface;
use NavBundle\Repository\RepositoryInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
interface ManagerInterface
{
    /**
     * Get an entity ClassMetadata.
     *
     * @param string $className the entity class
     *
     * @return ClassMetadataInterface the entity ClassMetadata
     */
    public function getClassMetadata(string $className);

    /**
     * Get the driver.
     *
     * @return ClassMetadataDriverInterface the ClassMetadataDriver object
     */
    public function getDriver();

    /**
     * Get the client for a class.
     *
     * @return \SoapClient the client object
     */
    public function getClient(string $className);

    /**
     * Get the entity repository.
     *
     * @param string $className the entity class
     *
     * @return RepositoryInterface the entity repository
     */
    public function getRepository(string $className);

    /**
     * Finds an entity by its primary key / identifier.
     *
     * @param string $className the entity class
     * @param string $no        the primary key / identifier
     *
     * @return object|null the entity instance or NULL if the entity cannot be found
     */
    public function find(string $className, string $no);

    /**
     * Finds all entities in the repository.
     *
     * @param string $className the entity class
     *
     * @return iterable the entities
     */
    public function findAll(string $className);

    /**
     * Finds entities by a set of criteria.
     *
     * @param string $className the entity class
     * @param array  $criteria  the criteria
     * @param int    $size      the size
     *
     * @return iterable the entities
     */
    public function findBy(string $className, array $criteria = [], int $size = 0);

    /**
     * Finds 1 entity by a set of criteria.
     *
     * @param string $className the entity class
     * @param array  $criteria  the criteria
     *
     * @return object|null the entity
     */
    public function findOneBy(string $className, array $criteria = []);

    /**
     * Creates an entity.
     *
     * @param object $entity the entity to create
     *
     * @return bool TRUE if the creation is successful, FALSE otherwise
     */
    public function create(object $entity);

    /**
     * Updates an entity.
     *
     * @param object $entity the entity to update
     *
     * @return bool TRUE if the update is successful, FALSE otherwise
     */
    public function update(object $entity);

    /**
     * Deletes an entity.
     *
     * @param object $entity the entity to delete
     *
     * @return bool TRUE if the deletion is successful, FALSE otherwise
     */
    public function delete(object $entity);
}
