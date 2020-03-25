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

namespace NavBundle\Bridge\ApiPlatform\DataProvider\Extension\Filter;

use ApiPlatform\Core\Api\IdentifiersExtractorInterface;
use ApiPlatform\Core\Api\IriConverterInterface;
use NavBundle\ClassMetadata\ClassMetadataInterface;
use NavBundle\Exception\InvalidArgumentException;
use NavBundle\PropertyInfo\Types;
use NavBundle\RegistryInterface;
use NavBundle\RequestBuilder\RequestBuilderInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
abstract class AbstractFilter implements FilterInterface
{
    protected $registry;
    protected $iriConverter;
    protected $identifiersExtractor;
    protected $nameConverter;
    protected $properties;

    public function __construct(RegistryInterface $registry, IriConverterInterface $iriConverter, IdentifiersExtractorInterface $identifiersExtractor, NameConverterInterface $nameConverter = null, array $properties = null)
    {
        $this->registry = $registry;
        $this->iriConverter = $iriConverter;
        $this->identifiersExtractor = $identifiersExtractor;
        $this->nameConverter = $nameConverter;
        $this->properties = $properties;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(RequestBuilderInterface $requestBuilder, string $resourceClass, string $operationName = null, array $context = []): void
    {
        foreach ($context['filters'] as $property => $value) {
            $property = $this->denormalizePropertyName($property);

            if (
                null === $value ||
                !$this->isPropertyEnabled($property, $resourceClass) ||
                !$this->isPropertyMapped($property, $resourceClass, true)
            ) {
                continue;
            }

            if ($this->isPropertyNested($property, $resourceClass)) {
                throw new InvalidArgumentException('Nested filters are not supported.');
            }

            $values = $this->normalizeValues((array) $value);
            if (null === $values) {
                continue;
            }

            $this->filterProperty($property, $values, $requestBuilder, $resourceClass, $operationName, $context);
        }
    }

    abstract protected function filterProperty(string $property, array $values, RequestBuilderInterface $requestBuilder, string $resourceClass, string $operationName = null, array $context = []): void;

    abstract protected function normalizeValues(array $values): ?array;

    protected function getClassMetadata(string $resourceClass): ClassMetadataInterface
    {
        return $this->registry->getManagerForClass($resourceClass)->getClassMetadata($resourceClass);
    }

    protected function isPropertyMapped(string $property, string $resourceClass, bool $allowAssociation = false): bool
    {
        if ($this->isPropertyNested($property, $resourceClass)) {
            throw new InvalidArgumentException('Nested filters are not supported.');
        }

        $metadata = $this->getClassMetadata($resourceClass);

        return $metadata->hasField($property) || ($allowAssociation && $metadata->hasAssociation($property));
    }

    protected function isPropertyNested(string $property, string $resourceClass): bool
    {
        $pos = strpos($property, '.');
        if (false === $pos) {
            return false;
        }

        return null !== $resourceClass && $this->getClassMetadata($resourceClass)->hasAssociation(substr($property, 0, $pos));
    }

    protected function isPropertyEnabled(string $property, string $resourceClass): bool
    {
        if (null === $this->properties) {
            // to ensure sanity, nested properties must still be explicitly enabled
            return !$this->isPropertyNested($property, $resourceClass);
        }

        return \array_key_exists($property, $this->properties);
    }

    protected function getType(string $type): string
    {
        switch ($type) {
            case Types::ARRAY:
                return 'array';
            case Types::SMALLINT:
            case Types::INT:
            case Types::INTEGER:
            case Types::BIGINT:
                return 'int';
            case Types::BOOLEAN:
            case Types::BOOL:
                return 'bool';
            case Types::DATE:
            case Types::DATETIME:
            case Types::DATETIMEZ:
            case Types::TIME:
            case Types::DATE_IMMUTABLE:
            case Types::DATETIME_IMMUTABLE:
            case Types::DATETIMEZ_IMMUTABLE:
            case Types::TIME_IMMUTABLE:
                return \DateTimeInterface::class;
            case Types::FLOAT:
                return 'float';
            default:
                return 'string';
        }
    }

    protected function normalizePropertyName(string $property): string
    {
        return !$this->nameConverter instanceof NameConverterInterface ? $property : $this->nameConverter->normalize($property);
    }

    protected function denormalizePropertyName(string $property): string
    {
        return !$this->nameConverter instanceof NameConverterInterface ? $property : $this->nameConverter->denormalize($property);
    }
}
