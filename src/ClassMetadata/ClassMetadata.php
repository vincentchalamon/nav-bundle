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
use NavBundle\EntityRepository\EntityRepository;
use NavBundle\Exception\AssociationNotFoundException;
use NavBundle\Exception\FieldNotFoundException;
use NavBundle\Exception\InvalidMethodCallException;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

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
    private $nameConverter;

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

    public function __construct(string $name, NameConverterInterface $nameConverter)
    {
        $this->name = $name;
        $this->nameConverter = $nameConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getReflectionClass()
    {
        return $this->reflClass;
    }

    /**
     * {@inheritdoc}
     */
    public function isIdentifier($fieldName)
    {
        return $this->identifier && $this->identifier === $fieldName;
    }

    /**
     * {@inheritdoc}
     */
    public function hasField($fieldName)
    {
        return isset($this->fieldMappings[$fieldName]);
    }

    /**
     * {@inheritdoc}
     */
    public function hasAssociation($fieldName)
    {
        return isset($this->associationMappings[$fieldName]);
    }

    /**
     * {@inheritdoc}
     */
    public function isSingleValuedAssociation($fieldName)
    {
        return isset($this->associationMappings[$fieldName]) && ($this->associationMappings[$fieldName]['type'] & self::TO_ONE);
    }

    /**
     * {@inheritdoc}
     */
    public function isCollectionValuedAssociation($fieldName)
    {
        return isset($this->associationMappings[$fieldName]) && ($this->associationMappings[$fieldName]['type'] & self::TO_MANY);
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldNames()
    {
        return array_keys($this->fieldMappings);
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldColumnName($fieldName)
    {
        if (!isset($this->fieldMappings[$fieldName])) {
            throw new FieldNotFoundException("Field name expected, '$fieldName' is not a field.");
        }

        return $this->fieldMappings[$fieldName]['columnName'];
    }

    /**
     * {@inheritdoc}
     */
    public function retrieveField($columnName)
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
    public function retrieveSingleValuedAssociation($columnName)
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
    public function getIdentifierFieldNames()
    {
        return [$this->identifier];
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociationNames()
    {
        return array_keys($this->associationMappings);
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeOfField($fieldName)
    {
        if (!isset($this->fieldMappings[$fieldName])) {
            throw new FieldNotFoundException("Field name expected, '$fieldName' is not a field.");
        }

        return $this->fieldMappings[$fieldName]['type'];
    }

    /**
     * {@inheritdoc}
     */
    public function isNullable($fieldName)
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
    public function getAssociationTargetClass($assocName)
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
    public function isAssociationInverseSide($assocName)
    {
        return isset($this->associationMappings[$assocName]) && !$this->associationMappings[$assocName]['isOwningSide'];
    }

    /**
     * {@inheritdoc}
     */
    public function getAssociationMappedByTargetField($assocName)
    {
        if (!isset($this->associationMappings[$assocName])) {
            throw new AssociationNotFoundException("Association name expected, '$assocName' is not an association.");
        }

        return $this->associationMappings[$assocName]['mappedBy'];
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getIdentifierValues($object)
    {
        throw new InvalidMethodCallException('Method getIdentifierValues() must not be called from ClassMetadata. You should invoke getIdentifierValue($object).');
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierValue($object)
    {
        if (!$this->identifier) {
            return null;
        }

        return $this->reflFields[$this->identifier]->getValue($object);
    }

    /**
     * {@inheritdoc}
     */
    public function getKeyValue($object)
    {
        if (!$this->key) {
            return null;
        }

        return $this->reflFields[$this->key]->getValue($object);
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityRepositoryClass()
    {
        return $this->repositoryClass;
    }

    /**
     * {@inheritdoc}
     */
    public function setEntityRepositoryClass(string $repositoryClass): void
    {
        $this->repositoryClass = $repositoryClass;
    }

    /**
     * {@inheritdoc}
     */
    public function setConnectionClass(string $connection): void
    {
        $this->connectionClass = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function getConnectionClass()
    {
        return $this->connectionClass;
    }

    /**
     * {@inheritdoc}
     */
    public function mapField(array $mapping): void
    {
        if (!isset($mapping['columnName'])) {
            $mapping['columnName'] = $this->nameConverter->normalize($mapping['fieldName']);
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
            $mapping['columnName'] = $this->nameConverter->normalize($mapping['fieldName'].'No');
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
            $mapping['columnName'] = $this->nameConverter->normalize($mapping['fieldName'].'No');
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
        $this->reflClass = $reflService->getClass($this->getName());
    }

    /**
     * {@inheritdoc}
     */
    public function wakeupReflection(ReflectionService $reflService): void
    {
        $this->reflClass = $reflService->getClass($this->getName());

        foreach ($this->fieldMappings as $field => $mapping) {
            $this->reflFields[$field] = $reflService->getAccessibleProperty($this->getName(), $field);
        }

        foreach ($this->associationMappings as $field => $mapping) {
            $this->reflFields[$field] = $reflService->getAccessibleProperty($this->getName(), $field);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * {@inheritdoc}
     */
    public function setNamespace($namespace): void
    {
        $this->namespace = $namespace;
    }

    /**
     * {@inheritdoc}
     */
    public function setEntityListeners($entityListeners): void
    {
        $this->entityListeners = $entityListeners;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityListeners()
    {
        return $this->entityListeners;
    }

    /**
     * {@inheritdoc}
     *
     * @return string|null
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function setIdentifier($identifier): void
    {
        $this->identifier = $identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function setKey($key): void
    {
        $this->key = $key;
    }
}
