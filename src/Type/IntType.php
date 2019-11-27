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

namespace NavBundle\Type;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class IntType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    final public function getBuiltInType(): string
    {
        return 'int';
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $type): bool
    {
        return \in_array($type, ['int', 'integer'], true);
    }
}