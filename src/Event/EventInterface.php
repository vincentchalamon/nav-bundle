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

namespace NavBundle\Event;

use NavBundle\EntityManager\EntityManagerInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
interface EventInterface
{
    /**
     * Retrieves the associated object.
     *
     * @return object
     */
    public function getObject();

    /**
     * Retrieves the associated EntityManager.
     *
     * @return EntityManagerInterface
     */
    public function getObjectManager();
}
