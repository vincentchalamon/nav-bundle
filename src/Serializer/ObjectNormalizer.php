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
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ObjectNormalizer implements ContextAwareDenormalizerInterface, DenormalizerAwareInterface, NormalizerInterface
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
    public function denormalize($values, $className, $format = null, array $context = []): ?object
    {
        if (!$values) {
            return null;
        }

        $classMetadata = $this->registry->getManagerForClass($className)->getClassMetadata($className);
        $data = [];
        foreach ($classMetadata->getMapping() as $property => $options) {
            if (!\array_key_exists($options['name'], $values)) {
                continue;
            }

            $data[$property] = $values[$options['name']];
        }

        /* @see \Symfony\Component\Serializer\Normalizer\ObjectNormalizer */
        return $this->denormalizer->denormalize($data, $className, $format, $context + [__CLASS__ => true]);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $className, $format = null, array $context = []): bool
    {
        try {
            return !isset($context[__CLASS__])
                && $this->registry->getManagerForClass($className) instanceof ManagerInterface;
        } catch (ClassMetadataNotFoundException $exception) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        $className = \get_class($object);
        $classMetadata = $this->registry->getManagerForClass($className)->getClassMetadata($className);
        $data = [];
        foreach ($classMetadata->getMapping() as $property => $options) {
            $data[$options['name']] = $object->{$property};
        }

        return [$classMetadata->getNamespace() => $data];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        try {
            return $this->registry->getManagerForClass(\get_class($data)) instanceof ManagerInterface;
        } catch (ClassMetadataNotFoundException $exception) {
            return false;
        }
    }
}
