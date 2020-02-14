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

use NavBundle\RequestBuilder\RequestBuilderInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
interface ResultCollectionExtensionInterface extends CollectionExtensionInterface
{
    /**
     * Change request builder before retrieving a collection of entities.
     *
     * @param RequestBuilderInterface $requestBuilder the request builder
     * @param string                  $resourceClass  the entity class
     * @param string|null             $operationName  the operation name
     * @param array                   $context        the context
     *
     * @return \Traversable the request builder result
     */
    public function getResult(RequestBuilderInterface $requestBuilder, string $resourceClass, string $operationName = null, array $context = []): \Traversable;

    /**
     * Checks whether this extension is supported.
     *
     * @param string      $resourceClass the entity class
     * @param string|null $operationName the operation name
     * @param array       $context       the context
     *
     * @return bool TRUE if it supports result, FALSE otherwise
     */
    public function supportsResult(string $resourceClass, string $operationName = null, array $context = []): bool;
}
