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

use ApiPlatform\Core\Exception\InvalidArgumentException as ApiInvalidArgumentException;
use NavBundle\Exception\InvalidArgumentException;
use NavBundle\PropertyInfo\Types;
use NavBundle\RequestBuilder\RequestBuilderInterface;
use NavBundle\Util\ClassUtils;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class SearchFilter extends AbstractFilter
{
    public const STRATEGY_EXACT = 'exact';
    public const STRATEGY_PARTIAL = 'partial';
    public const STRATEGY_START = 'start';
    public const STRATEGY_END = 'end';

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        $description = [];

        $properties = $this->properties;
        if (null === $properties) {
            $properties = array_fill_keys($this->getClassMetadata($resourceClass)->getFieldNames(), null);
        }

        foreach ($properties as $property => $strategy) {
            if (!$this->isPropertyMapped($property, $resourceClass, true)) {
                continue;
            }

            if ($this->isPropertyNested($property, $resourceClass)) {
                throw new InvalidArgumentException('Nested filters are not supported.');
            }

            $metadata = $this->getClassMetadata($resourceClass);
            $propertyName = $this->normalizePropertyName($property);
            if ($metadata->hasField($property)) {
                $typeOfField = $this->getType($metadata->getTypeOfField($property));
                $strategy = $this->properties[$property] ?? self::STRATEGY_EXACT;
                $filterParameterNames = [$propertyName];

                if (self::STRATEGY_EXACT === $strategy) {
                    $filterParameterNames[] = $propertyName.'[]';
                }

                foreach ($filterParameterNames as $filterParameterName) {
                    $description[$filterParameterName] = [
                        'property' => $propertyName,
                        'type' => $typeOfField,
                        'required' => false,
                        'strategy' => $strategy,
                        'is_collection' => '[]' === substr((string) $filterParameterName, -2),
                    ];
                }
            } elseif ($metadata->hasAssociation($property) && $metadata->isSingleValuedAssociation($property)) {
                $filterParameterNames = [
                    $propertyName,
                    $propertyName.'[]',
                ];

                foreach ($filterParameterNames as $filterParameterName) {
                    $description[$filterParameterName] = [
                        'property' => $propertyName,
                        'type' => 'string',
                        'required' => false,
                        'strategy' => self::STRATEGY_EXACT,
                        'is_collection' => '[]' === substr((string) $filterParameterName, -2),
                    ];
                }
            }
        }

        return $description;
    }

    /**
     * {@inheritdoc}
     */
    protected function filterProperty(string $property, array $values, RequestBuilderInterface $requestBuilder, string $resourceClass, string $operationName = null, array $context = []): void
    {
        $caseSensitive = true;
        $metadata = $this->getClassMetadata($resourceClass);

        if ($metadata->hasField($property)) {
            if ($metadata->isIdentifier($property)) {
                $values = array_map([$this, 'getIdentifierFromValue'], $values);
            }

            if (!$this->hasValidValues($values, $metadata->getTypeOfField($property))) {
                return;
            }

            $strategy = $this->properties[$property] ?? self::STRATEGY_EXACT;

            // prefixing the strategy with i makes it case insensitive
            if (0 === strpos($strategy, 'i')) {
                $strategy = substr($strategy, 1);
                $caseSensitive = false;
            }

            if (1 === \count($values)) {
                $this->addWhereByStrategy($strategy, $requestBuilder, $property, $values[0], $caseSensitive);

                return;
            }

            if (self::STRATEGY_EXACT !== $strategy) {
                return;
            }

            $requestBuilder->andWhere($property, implode('|', $values));
        }

        // metadata doesn't have the field, nor an association on the field
        if (!$metadata->hasAssociation($property)) {
            return;
        }

        $values = array_map([$this, 'getIdentifierFromValue'], $values);

        $associationResourceClass = $metadata->getAssociationTargetClass($property);
        $metadata = $this->getClassMetadata($associationResourceClass);
        $associationFieldIdentifier = $this->identifiersExtractor->getIdentifiersFromResourceClass($associationResourceClass)[0];
        $typeOfField = $metadata->getTypeOfField($associationFieldIdentifier);

        if (!$this->hasValidValues($values, $typeOfField)) {
            return;
        }

        if (!$metadata->isSingleValuedAssociation($property)) {
            throw new InvalidArgumentException('Collection associations filters are not supported.');
        }

        $requestBuilder->andWhere($property, 1 === \count($values) ? "'$values[0]'" : implode('|', $values));
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeValues(array $values): ?array
    {
        foreach ($values as $key => $value) {
            if (!\is_int($key) || !\is_string($value)) {
                unset($values[$key]);
            }
        }

        if (empty($values)) {
            return null;
        }

        return array_values($values);
    }

    private function getIdentifierFromValue(string $value): string
    {
        try {
            $item = $this->iriConverter->getItemFromIri($value, ['fetch_data' => false]);

            return $this->getClassMetadata(ClassUtils::getRealClass($item))->getIdentifierValue($item);
        } catch (ApiInvalidArgumentException $e) {
            // Do nothing, return the raw value
        }

        return $value;
    }

    private function hasValidValues(array $values, $type = null): bool
    {
        foreach ($values as $key => $value) {
            if (\in_array($type, [Types::INTEGER, Types::INT], true) && null !== $value && false === filter_var($value, \FILTER_VALIDATE_INT)) {
                return false;
            }
        }

        return true;
    }

    private function addWhereByStrategy(string $strategy, RequestBuilderInterface $requestBuilder, string $property, $value, bool $caseSensitive): void
    {
        switch ($strategy) {
            case null:
            case self::STRATEGY_EXACT:
                $requestBuilder->andWhere($property, $caseSensitive ? "'$value'" : "@'$value'");
                break;
            case self::STRATEGY_PARTIAL:
                $requestBuilder->andWhere($property, $caseSensitive ? "*$value*" : "@*$value*");
                break;
            case self::STRATEGY_START:
                $requestBuilder->andWhere($property, $caseSensitive ? "$value*" : "@$value*");
                break;
            case self::STRATEGY_END:
                $requestBuilder->andWhere($property, $caseSensitive ? "*$value" : "@*$value");
                break;
            default:
                throw new ApiInvalidArgumentException(sprintf('strategy %s does not exist.', $strategy));
        }
    }
}
