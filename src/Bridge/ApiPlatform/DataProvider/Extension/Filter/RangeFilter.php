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
use NavBundle\RequestBuilder\RequestBuilderInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class RangeFilter extends AbstractFilter
{
    public const STRATEGY_GREATER_THAN = 'gt';
    public const STRATEGY_GREATER_THAN_OR_EQUAL = 'gte';
    public const STRATEGY_LESS_THAN = 'lt';
    public const STRATEGY_LESS_THAN_OR_EQUAL = 'lte';

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

            $propertyName = $this->normalizePropertyName($property);

            $description += $this->getFilterDescription($propertyName, self::STRATEGY_GREATER_THAN);
            $description += $this->getFilterDescription($propertyName, self::STRATEGY_GREATER_THAN_OR_EQUAL);
            $description += $this->getFilterDescription($propertyName, self::STRATEGY_LESS_THAN);
            $description += $this->getFilterDescription($propertyName, self::STRATEGY_LESS_THAN_OR_EQUAL);
        }

        return $description;
    }

    private function getFilterDescription(string $propertyName, string $operator): array
    {
        return [
            sprintf('%s[%s]', $propertyName, $operator) => [
                'property' => $propertyName,
                'type' => 'string',
                'required' => false,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function filterProperty(string $property, array $values, RequestBuilderInterface $requestBuilder, string $resourceClass, string $operationName = null, array $context = []): void
    {
        if (!\is_array($values)) {
            return;
        }

        foreach ($values as $strategy => $value) {
            if (null === $value) {
                continue;
            }

            $this->addWhereByStrategy($strategy, $requestBuilder, $property, $value);
        }
    }

    protected function normalizeValues(array $values): ?array
    {
        $operators = [self::STRATEGY_GREATER_THAN, self::STRATEGY_GREATER_THAN_OR_EQUAL, self::STRATEGY_LESS_THAN, self::STRATEGY_LESS_THAN_OR_EQUAL];

        foreach ($values as $strategy => $value) {
            if (!\in_array($strategy, $operators, true)) {
                unset($values[$strategy]);
            }
        }

        if (empty($values)) {
            return null;
        }

        return $values;
    }

    private function addWhereByStrategy(string $strategy, RequestBuilderInterface $requestBuilder, string $property, $value): void
    {
        switch ($strategy) {
            case self::STRATEGY_GREATER_THAN:
                $requestBuilder->andWhere($property, ">$value");
                break;
            case self::STRATEGY_GREATER_THAN_OR_EQUAL:
                $requestBuilder->andWhere($property, ">=$value");
                break;
            case self::STRATEGY_LESS_THAN:
                $requestBuilder->andWhere($property, "<$value");
                break;
            case self::STRATEGY_LESS_THAN_OR_EQUAL:
                $requestBuilder->andWhere($property, "<=$value");
                break;
            default:
                throw new ApiInvalidArgumentException(sprintf('strategy %s does not exist.', $strategy));
        }
    }
}
