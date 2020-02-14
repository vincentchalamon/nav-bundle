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

use Doctrine\Persistence\Mapping\AbstractClassMetadataFactory;
use Doctrine\Persistence\Mapping\ClassMetadata as DoctrineClassMetadata;
use Doctrine\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Persistence\Mapping\ReflectionService;
use NavBundle\EntityManager\EntityManagerInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ClassMetadataFactory extends AbstractClassMetadataFactory
{
    /**
     * @var EntityManagerInterface|null
     */
    private $em;

    /**
     * @var MappingDriver|null
     */
    private $driver;

    public function setEntityManager(EntityManagerInterface $em): void
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(): void
    {
        $this->driver = $this->em->getMappingDriver();
        $this->initialized = true;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFqcnFromAlias($namespaceAlias, $simpleClassName): string
    {
        return $this->em->getEntityNamespace($namespaceAlias).'\\'.$simpleClassName;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDriver(): ?MappingDriver
    {
        return $this->driver;
    }

    /**
     * {@inheritdoc}
     *
     * @param ClassMetadataInterface $classMetadata
     */
    protected function wakeupReflection($classMetadata, ReflectionService $reflService): void
    {
        $classMetadata->wakeupReflection($reflService);
    }

    /**
     * {@inheritdoc}
     *
     * @param ClassMetadataInterface $classMetadata
     */
    protected function initializeReflection($classMetadata, ReflectionService $reflService): void
    {
        $classMetadata->initializeReflection($reflService);
    }

    /**
     * {@inheritdoc}
     */
    protected function isEntity(DoctrineClassMetadata $classMetadata): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param DoctrineClassMetadata $classMetadata
     */
    protected function doLoadMetadata($classMetadata, $parent, $rootEntityFound, array $nonSuperclassParents): void
    {
        $this->driver->loadMetadataForClass($classMetadata->getName(), $classMetadata);
    }

    /**
     * {@inheritdoc}
     */
    protected function newClassMetadataInstance($className): DoctrineClassMetadata
    {
        return new ClassMetadata($className, $this->em->getNameConverter());
    }
}
