<?php

declare(strict_types=1);

namespace NavBundle\Type;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
abstract class ObjectType implements TypeInterface
{
    /**
     * {@inheritDoc}
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
