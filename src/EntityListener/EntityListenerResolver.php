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

use Psr\Container\ContainerInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class EntityListenerResolver implements EntityListenerResolverInterface
{
    private $container;
    private $entityListeners = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(string $className): object
    {
        $className = $className = trim($className, '\\');

        if ($this->container->has($className)) {
            return $this->container->get($className);
        }

        return $this->entityListeners[$className] = new $className();
    }
}
