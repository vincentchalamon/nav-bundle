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

namespace NavBundle\Hydrator;

use NavBundle\ClassMetadata\ClassMetadataInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
interface HydratorInterface
{
    /**
     * Hydrate objects from a connection response.
     *
     * @param mixed                  $response      the connection response
     * @param ClassMetadataInterface $classMetadata the entity class metadata
     * @param array                  $context       the hydration context
     *
     * @return object|iterable
     */
    public function hydrateAll($response, ClassMetadataInterface $classMetadata, array $context = []);
}
