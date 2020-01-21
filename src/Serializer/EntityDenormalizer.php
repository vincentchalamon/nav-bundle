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
use NavBundle\Exception\FieldNotFoundException;
use NavBundle\RegistryInterface;
use ProxyManager\Factory\LazyLoadingGhostFactory;
use ProxyManager\Proxy\GhostObjectInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class EntityDenormalizer implements DenormalizerInterface
{
    private $registry;

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $type, $format = null, array $context = [])
    {
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $this->registry->getManagerForClass($type)->getClassMetadata($type);

        $object = $context['object_to_populate'] ?? (new LazyLoadingGhostFactory())->createProxy($type, function (
            GhostObjectInterface $ghostObject,
            string $method,
            array $parameters,
            &$initializer,
            array $properties
        ) use ($data, $classMetadata) {
            $initializer = null;

            foreach ($data as $key => $value) {
                // TODO: Map associations
                try {
                    $property = $classMetadata->retrieveField($key);
                } catch (FieldNotFoundException $exception) {
                    // Key does not match any property
                    continue;
                }

                $properties[$property] = $value;
            }

            return true;
        }, ['skippedProperties' => [$classMetadata->getIdentifier(), $classMetadata->getKey()]]);

        $classMetadata->reflFields[$classMetadata->getIdentifier()]->setValue($object, $data['No']);
        $classMetadata->reflFields[$classMetadata->getKey()]->setValue($object, $data['Key']);

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return NavDecoder::FORMAT === $format
            && \is_string($type)
            && class_exists($type)
            && null !== $this->registry->getManagerForClass($type);
    }
}
