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

namespace NavBundle\Manager;

use NavBundle\ClassMetadata\ClassMetadataInterface;
use NavBundle\Exception\EntityNotFoundException;
use NavBundle\Repository\NavRepositoryInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class NavManager implements NavManagerInterface
{
    private $classMetadata;
    private $client;
    private $repositories;

    public function __construct(
        ClassMetadataInterface $classMetadata,
        \SoapClient $client,
        iterable $repositories
    ) {
        $this->classMetadata = $classMetadata;
        $this->client = $client;
        $this->repositories = $repositories;
    }

    /**
     * {@inheritDoc}
     */
    public function hasClass(string $class): bool
    {
        try {
            $this->classMetadata->getClassMetadataInfo($class);

            return true;
        } catch (EntityNotFoundException $exception) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getRepository(string $class): NavRepositoryInterface
    {
        if (!$this->hasClass($class)) {
            throw new EntityNotFoundException();
        }

        $classMetadataInfo = $this->classMetadata->getClassMetadataInfo($class);
        $repositoryClass = $classMetadataInfo->getRepositoryClass();
        if (!isset($this->repositories[$repositoryClass])) {
            $this->repositories[$repositoryClass] = new $repositoryClass($this->client, $classMetadataInfo->getNamespace());
        }

        return $this->repositories[$repositoryClass];
    }

    /**
     * {@inheritDoc}
     */
    public function find(string $namespace, string $no)
    {
        return $this->client->__call('Read', [
            'No' => $no,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function findAll(string $namespace)
    {
        return $this->findBy($namespace);
    }

    /**
     * {@inheritDoc}
     */
    public function findBy(string $namespace, array $criteria = [], int $size = 0)
    {
        return $this->client->__call('ReadMultiple', [
            'filter' => $criteria,
            'size' => $size,
        ]);
    }
}
