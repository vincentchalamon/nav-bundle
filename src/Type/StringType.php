<?php

declare(strict_types=1);

namespace NavBundle\Type;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class StringType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    final public function getBuiltInType(): string
    {
        return 'string';
    }
}
