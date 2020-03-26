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

namespace NavBundle\Serializer\NameConverter;

use Symfony\Component\Serializer\NameConverter\AdvancedNameConverterInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

if (interface_exists(AdvancedNameConverterInterface::class)) {
    // Symfony 4.2 and upper
    abstract class PolyfillNameConverter extends CamelCaseToSnakeCaseNameConverter implements AdvancedNameConverterInterface
    {
    }
} else {
    // Symfony 4.1 and inferior
    abstract class PolyfillNameConverter extends CamelCaseToSnakeCaseNameConverter
    {
    }
}
