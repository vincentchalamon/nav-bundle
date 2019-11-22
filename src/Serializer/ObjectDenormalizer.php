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

use NavBundle\Exception\EntityNotFoundException;
use NavBundle\Manager\ManagerInterface;
use NavBundle\RegistryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class ObjectDenormalizer implements DenormalizerInterface
{
    private $registry;
    private $propertyAccessor;

    public function __construct(RegistryInterface $registry, PropertyAccessorInterface $propertyAccessor)
    {
        $this->registry = $registry;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = []): object
    {
        $rc = new \ReflectionClass($class);
        $object = $rc->newInstance();
        $classMetadataInfo = $this->registry
            ->getManagerForClass($class)
            ->getClassMetadata()
            ->getClassMetadataInfo($class);
        if (!isset($data[$classMetadataInfo->getNamespace()])) {
            return $object;
        }

        foreach ($classMetadataInfo->getMapping() as $property => $options) {
            $this->propertyAccessor->setValue(
                $object,
                $property,
                $data[$classMetadataInfo->getNamespace()][$options['name']] ?? null
            );
        }

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $class, $format = null): bool
    {
        try {
            return ObjectDecoder::FORMAT === $format && $this->registry->getManagerForClass($class) instanceof ManagerInterface;
        } catch (EntityNotFoundException $exception) {
            return false;
        }
    }
}
