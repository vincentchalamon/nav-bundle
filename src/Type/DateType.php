<?php

declare(strict_types=1);

namespace NavBundle\Type;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class DateType extends ObjectType
{
    /**
     * {@inheritDoc}
     */
    public function getClass(): string
    {
        return \DateTime::class;
    }

    /**
     * {@inheritDoc}
     */
    public function supports(string $type): bool
    {
        return in_array($type, ['date', 'datetime'], true);
    }
}
