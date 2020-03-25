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

namespace NavBundle\Bridge\ApiPlatform\DataProvider;

use ApiPlatform\Core\DataProvider\Pagination as ApiPlatformPagination;

/**
 * Pagination configuration decorated from ApiPlatform\Core\DataProvider\Pagination.
 *
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class Pagination
{
    private $pagination;
    private $options;

    public function __construct(ApiPlatformPagination $pagination, array $options = [])
    {
        $this->pagination = $pagination;
        $this->options = array_merge([
            'page_default' => null,
            'page_parameter_name' => 'page',
        ], $options);
    }

    /**
     * @see ApiPlatformPagination::isGraphQlEnabled
     */
    public function isGraphQlEnabled(?string $resourceClass = null, ?string $operationName = null, array $context = []): bool
    {
        return $this->pagination->isGraphQlEnabled($resourceClass, $operationName, $context);
    }

    /**
     * @see ApiPlatformPagination::isEnabled
     */
    public function isEnabled(string $resourceClass = null, string $operationName = null, array $context = []): bool
    {
        return $this->pagination->isEnabled($resourceClass, $operationName, $context);
    }

    public function getBookmarkKey(array $context = []): ?string
    {
        $filters = $context['filters'] ?? [];
        $parameterName = $this->options['page_parameter_name'];

        return \array_key_exists($parameterName, $filters) ? $filters[$parameterName] : $this->options['page_default'];
    }

    /**
     * @see ApiPlatformPagination::getLimit
     */
    public function getLimit(string $resourceClass = null, string $operationName = null, array $context = []): int
    {
        return $this->pagination->getLimit($resourceClass, $operationName, $context);
    }
}
