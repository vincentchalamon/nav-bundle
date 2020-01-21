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

use NavBundle\ClassMetadata\ClassMetadata;
use NavBundle\RegistryInterface;
use NavBundle\Util\ClassUtils;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class EntityNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private $registry;

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        $class = ClassUtils::getRealClass($object);
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $this->registry->getManagerForClass($class)->getClassMetadata($class);
        $data = [];
        if ($key = $classMetadata->getKeyValue($object)) {
            $data['Key'] = $key;
        }

        foreach ($classMetadata->reflFields as $fieldName => $refProp) {
            if (
                !($value = $refProp->getValue($object))
                || !isset($context['properties'])
                || !\array_key_exists($fieldName, $context['properties'])
            ) {
                continue;
            }

            $data[$classMetadata->getFieldColumnName($fieldName)] = $this->normalizer->normalize($value);
        }
        // TODO: Map associations

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return NavDecoder::FORMAT === $format
            && (\is_object($data) || (\is_string($data) && class_exists($data)))
            && null !== $this->registry->getManagerForClass(ClassUtils::getRealClass($data));
    }
}
