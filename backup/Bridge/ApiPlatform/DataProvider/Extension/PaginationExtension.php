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
use NavBundle\Bridge\ApiPlatform\DataProvider\ResultCollectionExtensionInterface;
use NavBundle\RequestBuilder\RequestBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class PaginationExtension implements ResultCollectionExtensionInterface
{
    private $requestStack;
    private $resourceMetadataFactory;

    public function __construct(RequestStack $requestStack, ResourceMetadataFactoryInterface $resourceMetadataFactory)
    {
        $this->requestStack = $requestStack;
        $this->resourceMetadataFactory = $resourceMetadataFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function applyToCollection(RequestBuilderInterface $builder, string $resourceClass, string $operationName = null, array $context = []): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return;
        }

        // TODO: Should be configurable.
        $builder->setFirstResult($request->query->get('bookmarkKey'));
        // TODO: Should be configurable.
        $builder->setMaxResults(10);
    }

    /**
     * {@inheritdoc}
     */
    public function getResult(RequestBuilderInterface $builder, string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        // TODO: Implement getResult() method.
    }

    /**
     * {@inheritdoc}
     */
    public function supportsResult(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        if (null === $request = $this->requestStack->getCurrentRequest()) {
            return false;
        }

        try {
            $resourceMetadata = $this->resourceMetadataFactory->create($resourceClass);
        } catch (ResourceClassNotFoundException $exception) {
            // TODO: Returns global API Platform configuration "isPaginationEnabled".
            return false;
        }

        $enabled = $resourceMetadata->getCollectionOperationAttribute($operationName, 'pagination_enabled', true, true);
        $clientEnabled = $resourceMetadata->getCollectionOperationAttribute($operationName, 'pagination_client_enabled', false, true);

        if ($clientEnabled) {
            if (null !== $paginationAttribute = $request->attributes->get('_api_pagination')) {
                //
                return \array_key_exists('pagination', $paginationAttribute) ? $paginationAttribute[$parameterName] : $default;
            }

            return $request->query->get($parameterName, $default);
            $enabled = filter_var($this->getPaginationParameter($request, 'pagination', $enabled), FILTER_VALIDATE_BOOLEAN);
        }

        return $enabled;
        return $this->isPaginationEnabled($request, $this->resourceMetadataFactory->create($resourceClass), $operationName);
        // TODO: isPaginationEnabled
    }
}
