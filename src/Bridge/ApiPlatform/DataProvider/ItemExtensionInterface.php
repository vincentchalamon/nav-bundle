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
interface ItemExtensionInterface
{
    /**
     * Change request builder before retrieving an entity.
     *
     * @param RequestBuilderInterface $requestBuilder the request builder
     * @param string                  $resourceClass  the entity class
     * @param string                  $identifier     the entity identifier
     * @param string|null             $operationName  the operation name
     * @param array                   $context        the context
     */
    public function applyToItem(RequestBuilderInterface $requestBuilder, string $resourceClass, string $identifier, string $operationName = null, array $context = []): void;
}
