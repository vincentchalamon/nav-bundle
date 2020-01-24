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

use Doctrine\Persistence\ManagerRegistry;
use NavBundle\Connection\ConnectionInterface;
use NavBundle\EntityManager\EntityManagerInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
interface RegistryInterface extends ManagerRegistry
{
    /**
     * {@inheritdoc}
     *
     * @return ConnectionInterface
     */
    public function getConnection($name = null);

    /**
     * {@inheritdoc}
     *
     * @return ConnectionInterface[]
     */
    public function getConnections();

    /**
     * {@inheritdoc}
     *
     * @return EntityManagerInterface
     */
    public function getManager($name = null);

    /**
     * {@inheritdoc}
     *
     * @return EntityManagerInterface[]
     */
    public function getManagers();

    /**
     * {@inheritdoc}
     *
     * @return EntityManagerInterface|null
     */
    public function getManagerForClass($class);
}
