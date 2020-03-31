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

use NavBundle\Exception\InvalidArgumentException;
use NavBundle\RequestBuilder\RequestBuilderInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class IntervalFilter extends AbstractFilter
{
    private const PATTERN_THROUGH = '/^[A-z\d ]+\.\.[A-z\d ]+$/';
    private const PATTERN_UP_TO = '/^\.\.[A-z\d ]+$/';
    private const PATTERN_FROM = '/^[A-z\d ]+\.\.$/';

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

            $description[sprintf('%s[]', $propertyName)] = [
                'property' => $propertyName,
                'type' => 'string',
                'required' => false,
            ];
        }

        return $description;
    }

    /**
     * {@inheritdoc}
     */
    protected function filterProperty(string $property, array $values, RequestBuilderInterface $requestBuilder, string $resourceClass, string $operationName = null, array $context = []): void
    {
        if (!\is_array($values)) {
            return;
        }

        foreach ($values as $value) {
            if (null === $value) {
                continue;
            }

            $requestBuilder->andWhere($property, $value);
        }
    }

    protected function normalizeValues(array $values): ?array
    {
        foreach ($values as $key => $value) {
            if (
                !preg_match(self::PATTERN_THROUGH, $value) &&
                !preg_match(self::PATTERN_UP_TO, $value) &&
                !preg_match(self::PATTERN_FROM, $value)
            ) {
                unset($values[$key]);
            }
        }

        if (empty($values)) {
            return null;
        }

        return $values;
    }
}
