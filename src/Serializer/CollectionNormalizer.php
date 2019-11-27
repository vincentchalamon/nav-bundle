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

use NavBundle\Exception\ClassMetadataNotFoundException;
use NavBundle\Manager\ManagerInterface;
use NavBundle\RegistryInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class CollectionNormalizer implements ContextAwareDenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private $registry;

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($values, $className, $format = null, array $context = []): \Generator
    {
        foreach ($values as $value) {
            yield $this->denormalizer->denormalize($value, $className, $format, $context + [__CLASS__ => true]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $className, $format = null, array $context = []): bool
    {
        try {
            return ReadMultipleResultDecoder::FORMAT === $format
                && !empty($data)
                && !isset($context[__CLASS__])
                && $this->registry->getManagerForClass($className) instanceof ManagerInterface;
        } catch (ClassMetadataNotFoundException $exception) {
            return false;
        }
    }
}
