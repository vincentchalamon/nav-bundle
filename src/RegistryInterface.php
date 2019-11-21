<?php

declare(strict_types=1);

namespace NavBundle;

use NavBundle\Exception\ManagerNotFoundException;
use NavBundle\Exception\RepositoryNotFoundException;
use NavBundle\Manager\NavManagerInterface;
use NavBundle\Repository\NavRepositoryInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
interface RegistryInterface
{
    /**
     * Get a manager by its name.
     *
     * @param string $name The manager name.
     *
     * @return NavManagerInterface The manager.
     *
     * @throws ManagerNotFoundException
     */
    public function getManager(string $name = 'default'): NavManagerInterface;

    /**
     * Get the manager related to the entity class.
     *
     * @param string $class The entity class.
     *
     * @return NavManagerInterface The manager related to this entity class.
     *
     * @throws ManagerNotFoundException
     */
    public function getManagerForClass(string $class): NavManagerInterface;

    /**
     * Get the repository related to the entity class.
     *
     * @param string $class The entity class.
     *
     * @return NavRepositoryInterface The repository related to this entity class.
     *
     * @throws RepositoryNotFoundException
     */
    public function getRepository(string $class): NavRepositoryInterface;
}
