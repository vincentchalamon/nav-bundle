<?php

declare(strict_types=1);

namespace NavBundle\Type;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
abstract class AbstractType implements TypeInterface
{
    /**
     * {@inheritDoc}
     */
    public function supports(string $type): bool
    {
        return $this->getBuiltInType() === $type;
    }

    /**
     * {@inheritDoc}
     */
    public function getClass(): ?string
    {
        return null;
    }
}
