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
final class ReadMultipleResultDecoder implements ContextAwareDecoderInterface
{
    public const FORMAT = 'ReadMultiple_Result';

    public function decode($object, $format, array $context = []): ?array
    {
        if (!isset($object->ReadMultiple_Result)
            || !isset($object->ReadMultiple_Result->{$context['namespace']})
            || empty((array) $object->ReadMultiple_Result->{$context['namespace']})
        ) {
            return null;
        }

        $results = $object->ReadMultiple_Result->{$context['namespace']};
        if (!is_array($results)) {
            $results = [$results];
        }

        $data = [];
        foreach ($results as $key => $value) {
            $data[$key] = (array) $value;
        }

        return $data;
    }

    public function supportsDecoding($format, array $context = []): bool
    {
        return self::FORMAT === $format && !empty($context['namespace']);
    }
}
