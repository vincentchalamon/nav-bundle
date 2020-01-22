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

use Doctrine\Persistence\Mapping\ClassMetadata as DoctrineClassMetadataInterface;
use Doctrine\Persistence\Mapping\ReflectionService;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
interface ClassMetadataInterface extends DoctrineClassMetadataInterface
{
    /**
     * Get the repository class configured for the entity.
     *
     * @return string
     */
    public function getEntityRepositoryClass();

    /**
     * Set the entity custom repository class.
     */
    public function setEntityRepositoryClass(string $repositoryClass): void;

    /**
     * Set the entity custom connection class.
     */
    public function setConnectionClass(string $connection): void;

    /**
     * Get the entity connection class.
     *
     * @return string
     */
    public function getConnectionClass();

    /**
     * Adds a mapped field to the class.
     *
     * @param array $mapping the field mapping
     */
    public function mapField(array $mapping): void;

    /**
     * Adds a one-to-one mapping.
     *
     * @param array $mapping the mapping
     */
    public function mapOneToOne(array $mapping): void;

    /**
     * Adds a one-to-many mapping.
     *
     * @param array $mapping the mapping
     */
    public function mapOneToMany(array $mapping): void;

    /**
     * Adds a many-to-one mapping.
     *
     * @param array $mapping the mapping
     */
    public function mapManyToOne(array $mapping): void;

    /**
     * Adds a many-to-many mapping.
     *
     * @param array $mapping the mapping
     */
    public function mapManyToMany(array $mapping): void;

    /**
     * Initializes Reflection after ClassMetadata was constructed.
     *
     * @param ReflectionService $reflService the reflection service
     */
    public function initializeReflection(ReflectionService $reflService): void;

    /**
     * Restores some state that can not be serialized/unserialized.
     *
     * @param ReflectionService $reflService the reflection service
     */
    public function wakeupReflection(ReflectionService $reflService): void;

    /**
     * Get the entity NAV namespace.
     *
     * @return string
     */
    public function getNamespace();

    /**
     * Set the entity NAV namespace.
     *
     * @param string $namespace the entity NAV namespace
     */
    public function setNamespace($namespace): void;

    /**
     * Set the entity listeners.
     *
     * @param string[] $entityListeners an array of entity listeners
     */
    public function setEntityListeners($entityListeners): void;

    /**
     * Get the entity listeners.
     *
     * @return string[] an array of entity listeners
     */
    public function getEntityListeners();

    /**
     * Gets the identifier value.
     *
     * @param object $object the entity
     *
     * @return string|null the identifier value
     */
    public function getIdentifierValue($object);

    /**
     * Gets the key value.
     *
     * @param object $object the entity
     *
     * @return string|null the identifier value
     */
    public function getKeyValue($object);

    /**
     * Gets the mapped identifier field name.
     *
     * @return string|null the identifier field name
     */
    public function getIdentifier();

    /**
     * INTERNAL:
     * Sets the mapped identifier/primary key field of this class.
     *
     * @param string $identifier the identifier
     */
    public function setIdentifier($identifier): void;

    /**
     * Gets the mapped key field name.
     *
     * @return string|null the key field name
     */
    public function getKey();

    /**
     * INTERNAL:
     * Sets the mapped key field of this class.
     *
     * @param string $key the key
     */
    public function setKey($key): void;

    /**
     * Gets the column name for the field.
     *
     * @param string $fieldName the field name
     *
     * @return string
     */
    public function getFieldColumnName($fieldName);

    /**
     * Gets the field name for the column.
     *
     * @param string $columnName the column name
     *
     * @return string
     */
    public function retrieveField($columnName);

    /**
     * Checks whether the field is nullable.
     *
     * @param string $fieldName the field name
     *
     * @return bool
     */
    public function isNullable($fieldName);
}
