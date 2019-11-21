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

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class ClassMetadataInfo implements ClassMetadataInfoInterface
{
    private $repositoryClass;
    private $namespace;

    public function __construct(string $repositoryClass, string $namespace)
    {
        $this->repositoryClass = $repositoryClass;
        $this->namespace = $namespace;
    }

    /**
     * {@inheritDoc}
     */
    public function getRepositoryClass(): string
    {
        return $this->repositoryClass;
    }

    /**
     * {@inheritDoc}
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }
}
