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

namespace Backup\NavBundle\Bridge\ApiPlatform\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class DataPersister implements DataPersisterInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports($data): bool
    {
        // TODO: Implement supports() method.
    }

    /**
     * {@inheritdoc}
     */
    public function persist($data): void
    {
        // TODO: Implement persist() method.
    }

    /**
     * {@inheritdoc}
     */
    public function remove($data): void
    {
        // TODO: Implement remove() method.
    }
}
