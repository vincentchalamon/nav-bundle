<?php

declare(strict_types=1);

namespace NavBundle\Type;

use NavBundle\Exception\TypeNotFoundException;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class TypeFactory
{
    /**
     * @var iterable|TypeInterface[]
     */
    private $types;

    public function __construct(iterable $types)
    {
        $this->types = $types;
    }

    /**
     * @param string $type
     *
     * @return TypeInterface
     *
     * @throws TypeNotFoundException
     */
    public function getType(string $type): TypeInterface
    {
        foreach ($this->types as $t) {
            if ($t->supports($type)) {
                return $t;
            }
        }

        throw new TypeNotFoundException();
    }
}
