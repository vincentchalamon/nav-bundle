<?php

declare(strict_types=1);

namespace NavBundle\Type;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
interface TypeInterface
{
    public function getBuiltInType(): string;

    public function supports(string $type): bool;

    public function getClass(): ?string;
}
