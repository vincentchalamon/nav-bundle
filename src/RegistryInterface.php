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

namespace NavBundle;

use NavBundle\Exception\ManagerNotFoundException;
use NavBundle\Manager\ManagerInterface;
use NavBundle\Repository\RepositoryInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
interface RegistryInterface
{
    /**
     * Get a manager by its name.
     *
     * @param string $name the manager name
     *
     * @throws ManagerNotFoundException
     *
     * @return ManagerInterface the manager
     */
    public function getManager(string $name = 'default'): ManagerInterface;

    /**
     * Get the manager related to the entity class.
     *
     * @param string $class the entity class
     *
     * @throws ManagerNotFoundException
     *
     * @return ManagerInterface the manager related to this entity class
     */
    public function getManagerForClass(string $class): ManagerInterface;

    /**
     * Get the repository related to the entity class.
     *
     * @param string $class the entity class
     *
     * @return RepositoryInterface the repository related to this entity class
     */
    public function getRepository(string $class): RepositoryInterface;
}
