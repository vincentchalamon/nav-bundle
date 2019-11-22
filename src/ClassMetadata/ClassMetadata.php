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
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ClassMetadata implements ClassMetadataInterface
{
    private $repositoryClass;
    private $namespace;
    private $mapping;

    public function __construct(string $repositoryClass, string $namespace, array $mapping)
    {
        $this->repositoryClass = $repositoryClass;
        $this->namespace = $namespace;
        $this->mapping = $mapping;
    }

    /**
     * {@inheritdoc}
     */
    public function getRepositoryClass(): string
    {
        return $this->repositoryClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * {@inheritdoc}
     */
    public function getMapping(): array
    {
        return $this->mapping;
    }
}
