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

use Doctrine\Persistence\Mapping\ReflectionService;
use NavBundle\Connection\Connection;
use NavBundle\EntityManager\EntityManagerInterface;
use NavBundle\EntityRepository\EntityRepository;
use NavBundle\Exception\AssociationNotFoundException;
use NavBundle\Exception\FieldNotFoundException;
use NavBundle\Exception\InvalidMethodCallException;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ClassMetadata implements ClassMetadataInterface
{
    /**
     * Identifies a one-to-one association.
     */
    public const ONE_TO_ONE = 1;

    /**
     * Identifies a many-to-one association.
     */
    public const MANY_TO_ONE = 2;

    /**
     * Identifies a one-to-many association.
     */
    public const ONE_TO_MANY = 4;

    /**
     * Combined bitmask for to-one (single-valued) associations.
     */
    public const TO_ONE = 3;

    /**
     * Combined bitmask for to-many (collection-valued) associations.
     */
    public const TO_MANY = 12;

    /**
     * Specifies that an association is to be fetched when it is first accessed.
     */
    public const FETCH_LAZY = 'lazy';

    /**
     * Specifies that an association is to be fetched when the owner of the association is fetched.
     */
    public const FETCH_EAGER = 'eager';

    /**
     * Specifies that an association is to be fetched lazy (on first access) and that commands such as Collection#count,
     * Collection#slice are issued directly against the database if the collection is not yet initialized.
     */
    public const FETCH_EXTRA_LAZY = 'extra_lazy';

    private $repositoryClass = EntityRepository::class;
    private $connectionClass = Connection::class;
    private $namespace;
    private $fieldMappings = [];
    private $associationMappings = [];
    private $name;
    private $em;

    /**
     * @var string|null
     */
    private $identifier;

    /**
     * @var string|null
     */
    private $key;

    /**
     * @var \ReflectionClass|null
     */
    private $reflClass;

    /**
     * @var \ReflectionProperty[]
     */
    public $reflFields = [];

    /**
     * @var string[]
     */
    private $entityListeners = [];

    public function __construct(string $name, EntityManagerInterface $em)
    {
        $this->name = $name;
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getReflectionClass(): \ReflectionClass
    {
        return $this->reflClass;
    }

    /**
     * {@inheritdoc}
     */
    public function isIdentifier($fieldName): bool
    {
        return $this->identifier && $this->identifier === $fieldName;
    }

    /**
     * {@inheritdoc}
     */
    public function hasField($fieldName): bool
    {
        return isset($this->fieldMappings[$fieldName]);
    }

    /**
     * {@inheritdoc}
     */
    public function hasAssociation($fieldName): bool
    {
        return isset($this->associationMappings[$fieldName]);
    }

    /**
     * {@inheritdoc}
     */
    public function isSingleValuedAssociation($fieldName): bool
    {
        return isset($this->associationMappings[$fieldName]) && ($this->associationMappings[$fieldName]['type'] & self::TO_ONE);
    }

    /**
     * {@inheritdoc}
     */
    public function isCollectionValuedAssociation($fieldName): bool
    {
        return isset($this->associationMappings[$fieldName]) && ($this->associationMappings[$fieldName]['type'] & self::TO_MANY);
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldNames(): array
    {
        return array_keys($this->fieldMappings);
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldColumnName($fieldName): string
    {
        if (!isset($this->fieldMappings[$fieldName])) {
            throw new FieldNotFoundException("Field name expected, '$fieldName' is not a field.");
        }

        return $this->fieldMappings[$fieldName]['columnName'];
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveField($columnName): string
    {
        foreach ($this->fieldMappings as $fieldName => $fieldMapping) {
            if ($columnName === $fieldMapping['columnName']) {
                return $fieldName;
            }
        }

        throw new FieldNotFoundException("No field found corresponding to column '$columnName'.");
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveSingleValuedAssociation($columnName): string
    {
        foreach ($this->associationMappings as $associationName => $associationMapping) {
            if ($this->isSingleValuedAssociation($associationName) && $columnName === $associationMapping['columnName']) {
                return $associationName;
            }
        }

        throw new AssociationNotFoundException("No single valued association found corresponding to column '$columnName'.");
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierFieldNames(): array
    {
        return [$this->identifier];
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociationNames(): array
    {
        return array_keys($this->associationMappings);
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeOfField($fieldName): string
    {
        if (!isset($this->fieldMappings[$fieldName])) {
            throw new FieldNotFoundException("Field name expected, '$fieldName' is not a field.");
        }

        return $this->fieldMappings[$fieldName]['type'];
    }

    /**
     * {@inheritdoc}
     */
    public function isNullable($fieldName): bool
    {
        if (isset($this->fieldMappings[$fieldName])) {
            return $this->fieldMappings[$fieldName]['nullable'];
        }

        if (isset($this->associationMappings[$fieldName])) {
            return $this->associationMappings[$fieldName]['nullable'] ?? true;
        }

        throw new FieldNotFoundException("Field name expected, '$fieldName' is not a field nor an association.");
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociationTargetClass($assocName): string
    {
        if (!isset($this->associationMappings[$assocName])) {
            throw new AssociationNotFoundException("Association name expected, '$assocName' is not an association.");
        }

        return $this->associationMappings[$assocName]['targetEntity'];
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociationFetchMode($assocName): string
    {
        if (!isset($this->associationMappings[$assocName])) {
            throw new AssociationNotFoundException("Association name expected, '$assocName' is not an association.");
        }

        return $this->associationMappings[$assocName]['fetch'];
    }

    /**
     * {@inheritdoc}
     */
    public function getSingleValuedAssociationColumnName($assocName): string
    {
        if (!isset($this->associationMappings[$assocName]) || !$this->isSingleValuedAssociation($assocName)) {
            throw new AssociationNotFoundException("Association name expected, '$assocName' is not an association or is not a single valued association.");
        }

        return $this->associationMappings[$assocName]['columnName'];
    }

    /**
     * {@inheritdoc}
     */
    public function isAssociationInverseSide($assocName): bool
    {
        return isset($this->associationMappings[$assocName]) && !$this->associationMappings[$assocName]['isOwningSide'];
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociationMappedByTargetField($assocName): string
    {
        if (!isset($this->associationMappings[$assocName])) {
            throw new AssociationNotFoundException("Association name expected, '$assocName' is not an association.");
        }

        return $this->associationMappings[$assocName]['mappedBy'];
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierValues($object): array
    {
        throw new InvalidMethodCallException('Method getIdentifierValues() must not be called from ClassMetadata. You should invoke getIdentifierValue($object).');
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierValue($object): ?string
    {
        if (!$this->identifier) {
            return null;
        }

        /* @see https://github.com/Ocramius/ProxyManager/pull/299 */
        return $this->em->getPropertyAccessor()->getValue($object, $this->identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function getKeyValue($object): ?string
    {
        if (!$this->key) {
            return null;
        }

        /* @see https://github.com/Ocramius/ProxyManager/pull/299 */
        return $this->em->getPropertyAccessor()->getValue($object, $this->key);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getEntityRepositoryClass(): string
    {
        return $this->repositoryClass;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function setEntityRepositoryClass(string $repositoryClass): void
    {
        $this->repositoryClass = $repositoryClass;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getConnectionClass(): string
    {
        return $this->connectionClass;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function setConnectionClass(string $connectionClass): void
    {
        $this->connectionClass = $connectionClass;
    }

    /**
     * {@inheritdoc}
     */
    public function mapField(array $mapping): void
    {
        if (!isset($mapping['columnName'])) {
            $mapping['columnName'] = $this->em->getNameConverter()->normalize($mapping['fieldName']);
        }
        $this->fieldMappings[$mapping['fieldName']] = $mapping;
    }

    /**
     * {@inheritdoc}
     */
    public function mapOneToOne(array $mapping): void
    {
        $mapping['isOwningSide'] = isset($mapping['mappedBy']) ? false : true;
        if ($mapping['isOwningSide'] && !isset($mapping['columnName'])) {
            $mapping['columnName'] = $this->em->getNameConverter()->normalize($mapping['fieldName'].'No');
        }
        $mapping['type'] = self::ONE_TO_ONE;
        $this->associationMappings[$mapping['fieldName']] = $mapping;
    }

    /**
     * {@inheritdoc}
     */
    public function mapManyToOne(array $mapping): void
    {
        if (!isset($mapping['columnName'])) {
            $mapping['columnName'] = $this->em->getNameConverter()->normalize($mapping['fieldName'].'No');
        }
        $mapping['isOwningSide'] = true;
        $mapping['type'] = self::MANY_TO_ONE;
        $this->associationMappings[$mapping['fieldName']] = $mapping;
    }

    /**
     * {@inheritdoc}
     */
    public function mapOneToMany(array $mapping): void
    {
        $mapping['isOwningSide'] = false;
        $mapping['type'] = self::ONE_TO_MANY;
        $this->associationMappings[$mapping['fieldName']] = $mapping;
    }

    /**
     * {@inheritdoc}
     */
    public function initializeReflection(ReflectionService $reflService): void
    {
        $this->reflClass = $reflService->getClass($this->name);
    }

    /**
     * {@inheritdoc}
     */
    public function wakeupReflection(ReflectionService $reflService): void
    {
        $this->reflClass = $reflService->getClass($this->name);

        foreach ($this->fieldMappings as $field => $mapping) {
            $this->reflFields[$field] = $reflService->getAccessibleProperty($this->name, $field);
        }

        foreach ($this->associationMappings as $field => $mapping) {
            $this->reflFields[$field] = $reflService->getAccessibleProperty($this->name, $field);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function setNamespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function setEntityListeners(array $entityListeners): void
    {
        $this->entityListeners = $entityListeners;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getEntityListeners(): array
    {
        return $this->entityListeners;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getKey(): ?string
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function setKey(string $key): void
    {
        $this->key = $key;
    }
}
