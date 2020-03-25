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

use NavBundle\Bridge\ApiPlatform\DataProvider\Pagination;
use NavBundle\Bridge\ApiPlatform\DataProvider\ResultCollectionExtensionInterface;
use NavBundle\RegistryInterface;
use NavBundle\RequestBuilder\RequestBuilderInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class PaginationExtension implements ResultCollectionExtensionInterface
{
    private $registry;
    private $pagination;

    public function __construct(RegistryInterface $registry, Pagination $pagination)
    {
        $this->registry = $registry;
        $this->pagination = $pagination;
    }

    /**
     * {@inheritdoc}
     */
    public function applyToCollection(RequestBuilderInterface $requestBuilder, string $resourceClass, string $operationName = null, array $context = []): void
    {
        if (!$this->pagination->isEnabled($resourceClass, $operationName, $context)) {
            return;
        }

        if (($context['graphql_operation_name'] ?? false) && !$this->pagination->isGraphQlEnabled($resourceClass, $operationName, $context)) {
            return;
        }

        if (($context['graphql_operation_name'] ?? false) && isset($context['filters']['last']) && !isset($context['filters']['before'])) {
            $context['count'] = $requestBuilder->count();
        }

        $requestBuilder
            ->setBookmarkKey($this->pagination->getBookmarkKey($context))
            ->setSize($this->pagination->getLimit($resourceClass, $operationName, $context));
    }

    /**
     * {@inheritdoc}
     */
    public function getResult(RequestBuilderInterface $requestBuilder, string $resourceClass, string $operationName = null, array $context = []): \Traversable
    {
        return $requestBuilder->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function supportsResult(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        if ($context['graphql_operation_name'] ?? false) {
            return $this->pagination->isGraphQlEnabled($resourceClass, $operationName, $context);
        }

        return $this->pagination->isEnabled($resourceClass, $operationName, $context);
    }
}
