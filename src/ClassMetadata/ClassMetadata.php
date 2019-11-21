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

namespace NavBundle\ClassMetadata;

use NavBundle\ClassMetadata\Driver\ClassMetadataDriverInterface;
use NavBundle\Exception\EntityNotFoundException;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class ClassMetadata implements ClassMetadataInterface
{
    private $driver;
    private $path;

    public function __construct(ClassMetadataDriverInterface $driver, string $path)
    {
        $this->driver = $driver;
        $this->path = $path;
    }

    /**
     * {@inheritDoc}
     */
    public function getClassMetadataInfo(string $class): ClassMetadataInfoInterface
    {
        if (!isset($this->getClassMetadataInfos()[$class])) {
            throw new EntityNotFoundException("Entity $class not found.");
        }

        return $this->getClassMetadataInfos()[$class];
    }

    /**
     * {@inheritDoc}
     */
    public function getClassMetadataInfos()
    {
        // todo Implement cache
        return $this->driver->getEntities($this->path);
    }
}
