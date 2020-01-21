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
use NavBundle\Annotation\Column;
use NavBundle\Annotation\Entity;
use NavBundle\Annotation\EntityListeners;
use NavBundle\Annotation\Id;
use NavBundle\Annotation\Key;
use NavBundle\ClassMetadata\ClassMetadataInterface;
use NavBundle\Exception\InvalidEntityException;
use NavBundle\Exception\PropertyTypeIsRequiredException;

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
     * @throws PropertyTypeIsRequiredException
     */
    public function loadMetadataForClass($className, $classMetadata): void
    {
        $reflectionClass = $classMetadata->getReflectionClass();

        // Evaluate Entity annotation
        /** @var Entity|null $classAnnotation */
        if (!$classAnnotation = $this->reader->getClassAnnotation($reflectionClass, Entity::class)) {
            throw new InvalidEntityException("Class '$className' is not a valid entity.");
        }

        $classMetadata->setNamespace($classAnnotation->namespace);
        $classMetadata->setEntityRepositoryClass($classAnnotation->repositoryClass);
        $classMetadata->setConnectionClass($classAnnotation->connection);

        // Evaluate EntityListeners annotation
        /** @var EntityListeners|null $entityListenersAnnotation */
        if ($entityListenersAnnotation = $this->reader->getClassAnnotation($reflectionClass, EntityListeners::class)) {
            $classMetadata->setEntityListeners($entityListenersAnnotation->listeners);
        }

        // Evaluate annotations on properties/fields
        foreach ($reflectionClass->getProperties() as $property) {
            /** @var Column|null $propertyAnnotation */
            if (!$propertyAnnotation = $this->reader->getPropertyAnnotation($property, Column::class)) {
                continue;
            }

            if (null === $propertyAnnotation->type) {
                throw new PropertyTypeIsRequiredException("The attribute 'type' is required for the column description of property $className::\${$property->getName()}.");
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

            // TODO: add ManyToOne association mapping
            // TODO: add ManyToMany association mapping
            // TODO: add OneToMany association mapping
            // TODO: add OneToOne association mapping
        }
    }
}
