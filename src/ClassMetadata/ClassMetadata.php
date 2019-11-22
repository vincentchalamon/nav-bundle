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
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ClassMetadata implements ClassMetadataInterface
{
    private $driver;
    private $path;
    private $classMetadataInfos;

    public function __construct(ClassMetadataDriverInterface $driver, string $path)
    {
        $this->driver = $driver;
        $this->path = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassMetadataInfo(string $class): ClassMetadataInfoInterface
    {
        if (!isset($this->getClassMetadataInfos()[$class])) {
            throw new EntityNotFoundException("Entity $class not found.");
        }

        return $this->getClassMetadataInfos()[$class];
    }

    /**
     * {@inheritdoc}
     */
    public function getClassMetadataInfos()
    {
        // todo Store objects in cache
        if (null === $this->classMetadataInfos) {
            $this->classMetadataInfos = $this->driver->getEntities($this->path);
        }

        return $this->classMetadataInfos;
    }
}
