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

namespace NavBundle\Serializer;

use Symfony\Component\Serializer\Encoder\ContextAwareDecoderInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ReadResultDecoder implements ContextAwareDecoderInterface
{
    public const FORMAT = 'Read_Result';

    public function decode($object, $format, array $context = []): ?array
    {
        if (!isset($object->{$context['namespace']}) || empty((array) $object->{$context['namespace']})) {
            return null;
        }

        return (array) $object->{$context['namespace']};
    }

    public function supportsDecoding($format, array $context = []): bool
    {
        return self::FORMAT === $format && !empty($context['namespace']);
    }
}
