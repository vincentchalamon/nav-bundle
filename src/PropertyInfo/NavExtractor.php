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

namespace NavBundle\PropertyInfo;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\Mapping\MappingException;
use NavBundle\ClassMetadata\ClassMetadata;
use NavBundle\ClassMetadata\ClassMetadataInterface;
use NavBundle\Collection\ExtraLazyCollection;
use NavBundle\Collection\LazyCollection;
use NavBundle\EntityManager\EntityManagerInterface;
use Symfony\Component\PropertyInfo\PropertyAccessExtractorInterface;
use Symfony\Component\PropertyInfo\PropertyListExtractorInterface;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\PropertyInfo\Type;

/**
 * Extracts data using NAV metadata.
 *
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class NavExtractor implements PropertyListExtractorInterface, PropertyTypeExtractorInterface, PropertyAccessExtractorInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getProperties($class, array $context = [])
    {
        if (null === $classMetadata = $this->getClassMetadata($class)) {
            return null;
        }

        return array_merge($classMetadata->getFieldNames(), $classMetadata->getAssociationNames());
    }

    /**
     * {@inheritdoc}
     */
    public function getTypes($class, $property, array $context = [])
    {
        if (
            null === ($classMetadata = $this->getClassMetadata($class))
            || (!$classMetadata->hasField($property) && !$classMetadata->hasAssociation($property))
        ) {
            return null;
        }

        if ($classMetadata->hasField($property)) {
            $typeOfField = $classMetadata->getTypeOfField($property);
            $nullable = $classMetadata->isNullable($property);

            switch ($typeOfField) {
                case Types::DATE:
                case Types::DATETIME:
                case Types::DATETIMEZ:
                case Types::TIME:
                    return [new Type(Type::BUILTIN_TYPE_OBJECT, $nullable, 'DateTime')];
                case Types::DATE_IMMUTABLE:
                case Types::DATETIME_IMMUTABLE:
                case Types::DATETIMEZ_IMMUTABLE:
                case Types::TIME_IMMUTABLE:
                    return [new Type(Type::BUILTIN_TYPE_OBJECT, $nullable, 'DateTimeImmutable')];
                case Types::ARRAY:
                    return [new Type(Type::BUILTIN_TYPE_ARRAY, $nullable, null, true)];
                default:
                    $builtinType = $this->getPhpType($typeOfField);

                    return $builtinType ? [new Type($builtinType, $nullable)] : null;
            }
        } elseif ($classMetadata->hasAssociation($property)) {
            if ($classMetadata->isSingleValuedAssociation($property)) {
                return [new Type(
                    Type::BUILTIN_TYPE_OBJECT,
                    $classMetadata->isNullable($property),
                    $classMetadata->getAssociationTargetClass($property)
                )];
            }

            switch ($classMetadata->getAssociationFetchMode($property)) {
                case ClassMetadata::FETCH_EAGER:
                    $collectionClass = ArrayCollection::class;
                    break;
                case ClassMetadata::FETCH_EXTRA_LAZY:
                    $collectionClass = ExtraLazyCollection::class;
                    break;
                default:
                case ClassMetadata::FETCH_LAZY:
                    $collectionClass = LazyCollection::class;
                    break;
            }

            return [new Type(
                Type::BUILTIN_TYPE_OBJECT,
                false,
                $collectionClass,
                true,
                null,
                new Type(
                    Type::BUILTIN_TYPE_OBJECT,
                    false,
                    $classMetadata->getAssociationTargetClass($property)
                )
            )];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable($class, $property, array $context = [])
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable($class, $property, array $context = [])
    {
        if (null === ($classMetadata = $this->getClassMetadata($class)) || $classMetadata->getIdentifier() !== $property) {
            return null;
        }

        return false;
    }

    private function getClassMetadata(string $class): ?ClassMetadataInterface
    {
        try {
            return $this->entityManager->getClassMetadata($class);
        } catch (MappingException $e) {
            return null;
        }
    }

    private function getPhpType(string $type): ?string
    {
        switch ($type) {
            case Types::SMALLINT:
            case Types::INT:
            case Types::INTEGER:
                return Type::BUILTIN_TYPE_INT;
            case Types::FLOAT:
                return Type::BUILTIN_TYPE_FLOAT;
            case Types::BIGINT:
            case Types::STRING:
            case Types::TEXT:
            case Types::GUID:
            case Types::DECIMAL:
                return Type::BUILTIN_TYPE_STRING;
            case Types::BOOL:
            case Types::BOOLEAN:
                return Type::BUILTIN_TYPE_BOOL;
            case Types::BLOB:
            case Types::BINARY:
                return Type::BUILTIN_TYPE_RESOURCE;
            case Types::OBJECT:
                return Type::BUILTIN_TYPE_OBJECT;
        }

        return null;
    }
}
