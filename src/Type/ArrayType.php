<?php

declare(strict_types=1);

namespace NavBundle\Type;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class ArrayType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    final public function getBuiltInType(): string
    {
        return 'array';
    }
}
