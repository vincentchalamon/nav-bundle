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

namespace NavBundle\Connection;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
interface ConnectionResolverInterface
{
    /**
     * Returns a connection instance for the given class name.
     *
     * @param string $className the entity connection class
     * @param string $namespace the entity NAV namespace
     *
     * @return ConnectionInterface a connection
     */
    public function resolve($className, $namespace);
}
