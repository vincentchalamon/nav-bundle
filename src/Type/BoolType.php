<?php

declare(strict_types=1);

namespace NavBundle\Type;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class BoolType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    final public function getBuiltInType(): string
    {
        return 'bool';
    }

    /**
     * {@inheritDoc}
     */
    public function supports(string $type): bool
    {
        return in_array($type, ['bool', 'boolean'], true);
    }
}
