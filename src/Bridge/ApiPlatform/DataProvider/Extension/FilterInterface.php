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

use NavBundle\RequestBuilder\RequestBuilderInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
interface FilterInterface
{
    /**
     * Apply filter on collection request builder.
     *
     * @param RequestBuilderInterface $builder       the request builder
     * @param string                  $resourceClass the entity resource class
     * @param string|null             $operationName the operation name
     * @param array                   $context       the context
     */
    public function apply(RequestBuilderInterface $builder, string $resourceClass, string $operationName = null, array $context = []): void;
}
