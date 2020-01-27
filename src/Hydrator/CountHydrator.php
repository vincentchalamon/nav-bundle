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
final class CountHydrator implements HydratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function hydrateAll($response, ClassMetadataInterface $classMetadata, array $context = []): int
    {
        if (!isset($response->ReadMultiple_Result->{$classMetadata->getNamespace()})) {
            return 0;
        }

        return \count($response->ReadMultiple_Result->{$classMetadata->getNamespace()});
    }
}
