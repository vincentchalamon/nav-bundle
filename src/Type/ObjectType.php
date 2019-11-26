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
abstract class ObjectType implements TypeInterface
{
    /**
     * {@inheritdoc}
     */
    final public function getBuiltInType(): string
    {
        return 'object';
    }

    /**
     * Get the object class.
     *
     * @return string the object class
     */
    abstract public function getClass(): string;
}
