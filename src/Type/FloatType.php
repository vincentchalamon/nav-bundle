<?php

declare(strict_types=1);

namespace NavBundle\Type;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class FloatType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    final public function getBuiltInType(): string
    {
        return 'float';
    }
}
