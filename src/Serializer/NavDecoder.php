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
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class NavDecoder implements DecoderInterface
{
    public const FORMAT = 'nav';

    /**
     * {@inheritdoc}
     */
    public function decode($data, $format, array $context = []): array
    {
        return $this->objectToArray($data);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDecoding($format): bool
    {
        return self::FORMAT === $format;
    }

    public function objectToArray($data)
    {
        if (\is_object($data)) {
            $data = get_object_vars($data);
        }

        if (\is_array($data)) {
            return array_map([$this, __FUNCTION__], $data);
        }

        return $data;
    }
}
