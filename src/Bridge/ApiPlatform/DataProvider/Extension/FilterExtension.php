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

use ApiPlatform\Core\Exception\ResourceClassNotFoundException;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use NavBundle\Bridge\ApiPlatform\DataProvider\CollectionExtensionInterface;
use NavBundle\Bridge\ApiPlatform\DataProvider\Extension\Filter\FilterInterface;
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
     */
    public function applyToCollection(RequestBuilderInterface $builder, string $resourceClass, string $operationName = null, array $context = []): void
    {
        try {
            $resourceFilters = $this->resourceMetadataFactory
                ->create($resourceClass)
                ->getCollectionOperationAttribute($operationName, 'filters', [], true);
        } catch (ResourceClassNotFoundException $exception) {
            return;
        }

        foreach ($resourceFilters as $filterId) {
            $filter = $this->filters->has($filterId) ? $this->filters->get($filterId) : null;
            if (!$filter instanceof FilterInterface) {
                continue;
            }

            $context['filters'] = $context['filters'] ?? [];
            $filter->apply($builder, $resourceClass, $operationName, $context);
        }
    }
}
