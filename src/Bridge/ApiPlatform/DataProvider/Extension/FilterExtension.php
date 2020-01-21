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

namespace NavBundle\Bridge\ApiPlatform\DataProvider\Extension;

use ApiPlatform\Core\Exception\InvalidArgumentException;
use ApiPlatform\Core\Exception\ResourceClassNotFoundException;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use NavBundle\Bridge\ApiPlatform\DataProvider\CollectionExtensionInterface;
use NavBundle\RequestBuilder\RequestBuilderInterface;
use Psr\Container\ContainerInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class FilterExtension implements CollectionExtensionInterface
{
    private $filters;
    private $resourceMetadataFactory;

    public function __construct(ResourceMetadataFactoryInterface $resourceMetadataFactory, ContainerInterface $filters)
    {
        $this->filters = $filters;
        $this->resourceMetadataFactory = $resourceMetadataFactory;
    }

    /**
     * {@inheritdoc}
     *
     * @throws ResourceClassNotFoundException
     * @throws InvalidArgumentException
     */
    public function applyToCollection(RequestBuilderInterface $builder, string $resourceClass, string $operationName = null, array $context = []): void
    {
        if (null === $resourceClass) {
            throw new InvalidArgumentException('The "$resourceClass" parameter must not be null');
        }

        $resourceMetadata = $this->resourceMetadataFactory->create($resourceClass);
        $resourceFilters = $resourceMetadata->getCollectionOperationAttribute($operationName, 'filters', [], true);

        if (empty($resourceFilters)) {
            return;
        }

        foreach ($resourceFilters as $filterId) {
            $filter = $this->filters->has($filterId) ? $this->filters->get($filterId) : null;
            if ($filter instanceof FilterInterface) {
                $context['filters'] = $context['filters'] ?? [];
                $filter->apply($builder, $resourceClass, $operationName, $context);
            }
        }
    }
}
