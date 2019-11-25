<?php

declare(strict_types=1);

namespace NavBundle\Type;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class IntType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    final public function getBuiltInType(): string
    {
        return 'int';
    }

    /**
     * {@inheritDoc}
     */
    public function supports(string $type): bool
    {
        return in_array($type, ['int', 'integer'], true);
    }
}
