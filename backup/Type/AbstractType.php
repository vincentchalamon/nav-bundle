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

namespace Backup\NavBundle\Type;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
abstract class AbstractType implements TypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(string $type): bool
    {
        return $this->getBuiltInType() === $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getClass(): ?string
    {
        return null;
    }
}
