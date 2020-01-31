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

use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class CollectionDenormalizer implements ContextAwareDenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $type, $format = null, array $context = []): \Iterator
    {
        $iterator = new \ArrayIterator();
        $data = $data['ReadMultiple_Result'][$context[ObjectDenormalizer::NAMESPACE]] ?? null;

        if (!$data) {
            return $iterator;
        }

        if (!isset($data[0])) {
            $data = [$data];
        }

        foreach ($data as $key => $result) {
            $iterator->append($this->denormalizer->denormalize($result, $type, $format, $context));
        }

        return $iterator;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return NavDecoder::FORMAT === $format && \is_array($data) && isset($data['ReadMultiple_Result']);
    }
}
