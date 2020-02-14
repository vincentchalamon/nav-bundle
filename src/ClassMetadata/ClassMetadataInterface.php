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
     */
    public function getEntityRepositoryClass(): string;

    /**
     * Set the entity custom repository class.
     *
     * @param string $repositoryClass the repository class
     */
    public function setEntityRepositoryClass(string $repositoryClass): void;

    /**
     * Get the connection class configured for the entity.
     */
    public function getConnectionClass(): string;

    /**
     * Set the entity custom connection class.
     *
     * @param string $connectionClass the connection class
     */
    public function setConnectionClass(string $connectionClass): void;

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
     */
    public function getNamespace(): string;

    /**
     * Set the entity NAV namespace.
     *
     * @param string $namespace the entity NAV namespace
     */
    public function setNamespace(string $namespace): void;

    /**
     * Set the entity listeners.
     *
     * @param string[] $entityListeners an array of entity listeners
     */
    public function setEntityListeners(array $entityListeners): void;

    /**
     * Get the entity listeners.
     *
     * @return string[] an array of entity listeners
     */
    public function getEntityListeners(): array;

    /**
     * Gets the identifier value.
     *
     * @param object $object the entity
     *
     * @return string|null the identifier value
     */
    public function getIdentifierValue($object): ?string;

    /**
     * Gets the key value.
     *
     * @param object $object the entity
     *
     * @return string|null the identifier value
     */
    public function getKeyValue($object): ?string;

    /**
     * Gets the mapped identifier field name.
     *
     * @return string|null the identifier field name
     */
    public function getIdentifier(): ?string;

    /**
     * INTERNAL:
     * Sets the mapped identifier/primary key field of this class.
     *
     * @param string $identifier the identifier
     */
    public function setIdentifier(string $identifier): void;

    /**
     * Gets the mapped key field name.
     *
     * @return string|null the key field name
     */
    public function getKey(): ?string;

    /**
     * INTERNAL:
     * Sets the mapped key field of this class.
     *
     * @param string $key the key
     */
    public function setKey(string $key): void;

    /**
     * Gets the column name for the field.
     *
     * @param string $fieldName the field name
     *
     * @return string the column name
     */
    public function getFieldColumnName(string $fieldName): string;

    /**
     * Gets the field name for the column.
     *
     * @param string $columnName the column name
     *
     * @return string the field name
     */
    public function retrieveField(string $columnName): string;

    /**
     * Gets the single valued association name for the column.
     *
     * @param string $columnName the column name
     *
     * @return string the association name
     */
    public function retrieveSingleValuedAssociation(string $columnName): string;

    /**
     * Checks whether the field is nullable.
     *
     * @param string $fieldName the field name
     *
     * @return bool TRUE if nullable, FALSE otherwise
     */
    public function isNullable(string $fieldName): bool;

    /**
     * Gets the association fetch mode.
     *
     * @param string $assocName the association name
     *
     * @return string the fetch mode
     */
    public function getAssociationFetchMode(string $assocName): string;

    /**
     * Gets the association column name.
     *
     * @param string $assocName the association name
     *
     * @return string the column name
     */
    public function getSingleValuedAssociationColumnName(string $assocName): string;
}
