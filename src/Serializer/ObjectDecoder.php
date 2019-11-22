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

use Symfony\Component\Serializer\Encoder\DecoderInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class ObjectDecoder implements DecoderInterface
{
    public const FORMAT = 'stdClass';

    public function decode($object, $format, array $context = []): array
    {
        $data = [];
        foreach ((array) $object as $key => $value) {
            if (\is_object($value)) {
                $value = $this->decode($value, $format, $context);
            }

            $data[$key] = $value;
        }

        return $data;
    }

    public function supportsDecoding($format): bool
    {
        return self::FORMAT === $format;
    }
}
