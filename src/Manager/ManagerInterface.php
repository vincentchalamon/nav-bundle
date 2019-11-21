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
     * Get the entity repository.
     *
     * @param string $class the entity class
     *
     * @return RepositoryInterface the entity repository
     */
    public function getRepository(string $class): RepositoryInterface;

    /**
     * Finds an entity by its primary key / identifier.
     *
     * @param \SoapClient $client the SOAP client
     * @param string      $no     the primary key / identifier
     *
     * @return object|null the entity instance or NULL if the entity cannot be found
     */
    public function find(\SoapClient $client, string $no);

    /**
     * Finds all entities in the repository.
     *
     * @param \SoapClient $client
     *
     * @return array the entities
     */
    public function findAll(\SoapClient $client);

    /**
     * Finds entities by a set of criteria.
     *
     * @param \SoapClient $client   the SOAP client
     * @param array       $criteria the criteria
     * @param int         $size     the size
     *
     * @return array the entities
     */
    public function findBy(\SoapClient $client, array $criteria = [], int $size = 0);
}
