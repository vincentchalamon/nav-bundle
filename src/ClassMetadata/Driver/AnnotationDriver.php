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

namespace NavBundle\ClassMetadata\Driver;

use Doctrine\Persistence\Mapping\Driver\AnnotationDriver as AbstractAnnotationDriver;
use Doctrine\Persistence\Mapping\MappingException;
use NavBundle\Annotation\Association;
use NavBundle\Annotation\Column;
use NavBundle\Annotation\Entity;
use NavBundle\Annotation\EntityListeners;
use NavBundle\Annotation\Id;
use NavBundle\Annotation\Key;
use NavBundle\Annotation\ManyToOne;
use NavBundle\Annotation\OneToMany;
use NavBundle\Annotation\OneToOne;
use NavBundle\ClassMetadata\ClassMetadataInterface;
use NavBundle\Exception\InvalidEntityException;
use NavBundle\Exception\PropertyIsRequiredException;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class AnnotationDriver extends AbstractAnnotationDriver
{
    /**
     * {@inheritdoc}
     */
    protected $entityAnnotationClasses = [Entity::class => 1];

    /**
     * {@inheritdoc}
     *
     * @param ClassMetadataInterface $classMetadata
     *
     * @throws InvalidEntityException
     * @throws PropertyIsRequiredException
     * @throws MappingException
     */
    public function loadMetadataForClass($className, $classMetadata): void
    {
        $reflectionClass = $classMetadata->getReflectionClass();

        // Evaluate Entity annotation
        /** @var Entity|null $classAnnotation */
        $classAnnotation = $this->reader->getClassAnnotation($reflectionClass, Entity::class);
        if (!$classAnnotation) {
            throw new InvalidEntityException("Class '$className' is not a valid entity.");
        }

        $classMetadata->setNamespace($classAnnotation->namespace);
        if ($repositoryClass = $classAnnotation->repositoryClass) {
            $classMetadata->setEntityRepositoryClass($repositoryClass);
        }
        if ($connectionClass = $classAnnotation->connectionClass) {
            $classMetadata->setConnectionClass($connectionClass);
        }

        // Evaluate EntityListeners annotation
        /** @var EntityListeners|null $entityListenersAnnotation */
        $entityListenersAnnotation = $this->reader->getClassAnnotation($reflectionClass, EntityListeners::class);
        if ($entityListenersAnnotation) {
            $classMetadata->setEntityListeners($entityListenersAnnotation->listeners);
        }

        // Evaluate annotations on properties/fields
        foreach ($reflectionClass->getProperties() as $property) {
            $propertyAnnotations = $this->reader->getPropertyAnnotations($property);
            foreach ($propertyAnnotations as $propertyAnnotation) {
                if (!$propertyAnnotation instanceof Column && !$propertyAnnotation instanceof Association) {
                    continue;
                }

                // It's a field
                if ($propertyAnnotation instanceof Column) {
                    if (null === $propertyAnnotation->type) {
                        throw new PropertyIsRequiredException("The attribute 'type' is required for the column description of property $className::\${$property->getName()}.");
                    }

                    $mapping = [
                        'fieldName' => $property->getName(),
                        'type' => $propertyAnnotation->type,
                        'nullable' => $propertyAnnotation->nullable,
                    ];

                    if ($this->reader->getPropertyAnnotation($property, Id::class)) {
                        $classMetadata->setIdentifier($mapping['fieldName']);

                        // Must be hardcoded cause its name never change on Nav
                        $mapping['columnName'] = 'No';
                    }

                    if ($this->reader->getPropertyAnnotation($property, Key::class)) {
                        $classMetadata->setKey($mapping['fieldName']);

                        // Must be hardcoded cause its name never change on Nav
                        $mapping['columnName'] = 'Key';
                    }

                    if ($name = $propertyAnnotation->name) {
                        $mapping['columnName'] = $name;
                    }

                    $classMetadata->mapField($mapping);
                    continue;
                }

                // It's an association
                if (null === $propertyAnnotation->targetClass) {
                    throw new PropertyIsRequiredException("The attribute 'targetClass' is required for the column description of property $className::\${$property->getName()}.");
                }

                $mapping = [
                    'fieldName' => $property->getName(),
                    'targetEntity' => $propertyAnnotation->targetClass,
                    'cascade' => $propertyAnnotation->cascade,
                    'fetch' => $propertyAnnotation->fetch,
                ];

                switch (true) {
                    case $propertyAnnotation instanceof ManyToOne:
                        $mapping['nullable'] = $propertyAnnotation->nullable;
                        if ($name = $propertyAnnotation->columnName) {
                            $mapping['columnName'] = $name;
                        }
                        if ($inversedBy = $propertyAnnotation->inversedBy) {
                            $mapping['inversedBy'] = $inversedBy;
                        }

                        $classMetadata->mapManyToOne($mapping);
                        break;
                    case $propertyAnnotation instanceof OneToMany:
                        $mapping['nullable'] = false;
                        $mapping['mappedBy'] = $propertyAnnotation->mappedBy;

                        $classMetadata->mapOneToMany($mapping);
                        break;
                    case $propertyAnnotation instanceof OneToOne:
                        $mapping['nullable'] = $propertyAnnotation->nullable;
                        if ($name = $propertyAnnotation->columnName) {
                            $mapping['columnName'] = $name;
                        }
                        if ($mappedBy = $propertyAnnotation->mappedBy) {
                            if (isset($mapping['columnName'])) {
                                throw new MappingException("Invalid mapping for association '$className::{$property->getName()}': columnName is only supported for owning side association.");
                            }
                            $mapping['mappedBy'] = $mappedBy;
                        } elseif ($inversedBy = $propertyAnnotation->inversedBy) {
                            $mapping['inversedBy'] = $inversedBy;
                        }

                        $classMetadata->mapOneToOne($mapping);
                        break;
                }
            }
        }
    }
}
