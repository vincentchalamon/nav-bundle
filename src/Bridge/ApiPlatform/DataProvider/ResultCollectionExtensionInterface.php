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
     * @param RequestBuilderInterface $builder       the request builder
     * @param string                  $resourceClass the entity class
     * @param string|null             $operationName the operation name
     * @param array                   $context       the context
     *
     * @return \Iterator|array<object>
     */
    public function getResult(RequestBuilderInterface $builder, string $resourceClass, string $operationName = null, array $context = []);

    /**
     * Checks whether this extension is supported.
     *
     * @param string      $resourceClass the entity class
     * @param string|null $operationName the operation name
     * @param array       $context       the context
     *
     * @return bool
     */
    public function supportsResult(string $resourceClass, string $operationName = null, array $context = []);
}
