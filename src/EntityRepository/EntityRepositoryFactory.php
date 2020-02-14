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

namespace NavBundle\EntityRepository;

use Doctrine\Persistence\ObjectRepository;
use NavBundle\EntityManager\EntityManagerInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class EntityRepositoryFactory implements EntityRepositoryFactoryInterface
{
    /**
     * @var ObjectRepository[]
     */
    private $entityRepositories = [];

    public function __construct(iterable $repositories)
    {
        foreach ($repositories as $repository) {
            $this->entityRepositories[\get_class($repository)] = $repository;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository(EntityManagerInterface $entityManager, string $className): ObjectRepository
    {
        $classMetadata = $entityManager->getClassMetadata($className);
        $entityRepositoryClass = $classMetadata->getEntityRepositoryClass();

        if (isset($this->entityRepositories[$entityRepositoryClass])) {
            return $this->entityRepositories[$entityRepositoryClass];
        }

        $repositoryHash = $classMetadata->getName().spl_object_hash($entityManager);

        if (isset($this->entityRepositories[$repositoryHash])) {
            return $this->entityRepositories[$repositoryHash];
        }

        return $this->entityRepositories[$repositoryHash] = $this->createRepository($entityManager, $className);
    }

    /**
     * Create a new repository instance for an entity class.
     *
     * @param EntityManagerInterface $entityManager the manager instance
     * @param string                 $className     the name of the entity
     *
     * @return ObjectRepository the repository
     */
    private function createRepository(EntityManagerInterface $entityManager, string $className): ObjectRepository
    {
        $repositoryClassName = $entityManager->getClassMetadata($className)->getEntityRepositoryClass();

        return new $repositoryClassName($entityManager, $className);
    }
}
