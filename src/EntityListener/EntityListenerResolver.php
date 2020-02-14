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
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class EntityListenerResolver implements EntityListenerResolverInterface
{
    private $entityListeners = [];

    public function __construct(iterable $entityListeners)
    {
        foreach ($entityListeners as $entityListener) {
            $this->entityListeners[\get_class($entityListener)] = $entityListener;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(string $className): object
    {
        if (isset($this->entityListeners[$className = trim($className, '\\')])) {
            return $this->entityListeners[$className];
        }

        return $this->entityListeners[$className] = new $className();
    }
}
