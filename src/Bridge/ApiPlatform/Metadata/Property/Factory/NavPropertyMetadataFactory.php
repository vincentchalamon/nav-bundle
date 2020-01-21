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

namespace NavBundle\Bridge\ApiPlatform\Metadata\Property\Factory;

use ApiPlatform\Core\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Property\PropertyMetadata;
use NavBundle\RegistryInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class NavPropertyMetadataFactory implements PropertyMetadataFactoryInterface
{
    private $decorated;
    private $registry;

    public function __construct(PropertyMetadataFactoryInterface $decorated, RegistryInterface $registry)
    {
        $this->decorated = $decorated;
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $resourceClass, string $property, array $options = []): PropertyMetadata
    {
        $propertyMetadata = $this->decorated->create($resourceClass, $property, $options);

        if (null !== $propertyMetadata->isIdentifier()) {
            return $propertyMetadata;
        }

        $manager = $this->registry->getManagerForClass($resourceClass);
        if (!$manager) {
            return $propertyMetadata;
        }
        $classMetadata = $manager->getClassMetadata($resourceClass);

        if ($classMetadata->getIdentifier() === $property) {
            $propertyMetadata = $propertyMetadata->withIdentifier(true);
            if (null === $propertyMetadata->isWritable()) {
                $propertyMetadata = $propertyMetadata->withWritable(false);
            }
        }

        if (null === $propertyMetadata->isIdentifier()) {
            $propertyMetadata = $propertyMetadata->withIdentifier(false);
        }

        return $propertyMetadata;
    }
}
