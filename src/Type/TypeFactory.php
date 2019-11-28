<?php

/*
 * This file is part of the NavBundle.
 *
 * (c) Vincent Chalamon <vincentchalamon@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace NavBundle\Type;

use NavBundle\Exception\TypeNotFoundException;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class TypeFactory
{
    private $types;

    /**
     * @param iterable|TypeInterface[]
     */
    public function __construct(iterable $types)
    {
        $this->types = $types;
    }

    /**
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
