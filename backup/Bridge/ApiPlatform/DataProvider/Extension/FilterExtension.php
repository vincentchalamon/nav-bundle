<?php

declare(strict_types=1);

namespace Backup\NavBundle\Bridge\ApiPlatform\DataProvider\Extension;

use ApiPlatform\Core\Exception\InvalidArgumentException;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use Backup\NavBundle\Bridge\ApiPlatform\DataProvider\CollectionExtensionInterface;
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
     * {@inheritDoc}
     */
    public function applyToCollection(array $criteria, string $resourceClass, string $operationName = null, array $context = [])
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
                $filter->apply($criteria, $resourceClass, $operationName, $context);
            }
        }
    }
}
