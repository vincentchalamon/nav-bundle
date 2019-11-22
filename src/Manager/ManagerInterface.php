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
    public function getClassMetadata(string $className): ClassMetadataInterface;

    /**
     * Get the driver.
     *
     * @return ClassMetadataDriverInterface the ClassMetadataDriver object
     */
    public function getDriver(): ClassMetadataDriverInterface;

    /**
     * Get the Client for a class.
     *
     * @return Client the Client object
     */
    public function getClient(string $className): Client;

    /**
     * Get the entity repository.
     *
     * @param string $className the entity class
     *
     * @return RepositoryInterface the entity repository
     */
    public function getRepository(string $className): RepositoryInterface;

    /**
     * Finds an entity by its primary key / identifier.
     *
     * @param string $className the entity class
     * @param string $id        the primary key / identifier
     *
     * @return object|null the entity instance or NULL if the entity cannot be found
     */
    public function find(string $className, string $id);

    /**
     * Finds all entities in the repository.
     *
     * @param string $className the entity class
     *
     * @return array the entities
     */
    public function findAll(string $className);

    /**
     * Finds entities by a set of criteria.
     *
     * @param string $className the entity class
     * @param array  $criteria  the criteria
     * @param int    $size      the size
     *
     * @return array the entities
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
}
