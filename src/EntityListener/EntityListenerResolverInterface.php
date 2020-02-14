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

namespace NavBundle\EntityListener;

/**
 * A resolver is used to instantiate an entity listener.
 *
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
interface EntityListenerResolverInterface
{
    /**
     * Returns a entity listener instance for the given class name.
     *
     * @param string $className the fully-qualified class name
     *
     * @return object an entity listener
     */
    public function resolve(string $className): object;
}
