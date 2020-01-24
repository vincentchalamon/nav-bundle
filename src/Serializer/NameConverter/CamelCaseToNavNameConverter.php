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

namespace NavBundle\Serializer\NameConverter;

use NavBundle\ClassMetadata\ClassMetadataInterface;
use NavBundle\Exception\AssociationNotFoundException;
use NavBundle\Exception\FieldNotFoundException;
use NavBundle\RegistryInterface;
use Symfony\Component\Serializer\NameConverter\AdvancedNameConverterInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class CamelCaseToNavNameConverter extends CamelCaseToSnakeCaseNameConverter implements AdvancedNameConverterInterface
{
    private $registry;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct();

        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($propertyName, string $class = null, string $format = null, array $context = [])
    {
        if ($class) {
            /** @var ClassMetadataInterface $classMetadata */
            $classMetadata = $this->registry->getManagerForClass($class)->getClassMetadata($class);

            if ($classMetadata->hasField($propertyName)) {
                return $classMetadata->getFieldColumnName($propertyName);
            } elseif ($classMetadata->hasAssociation($propertyName)) {
                if (!$classMetadata->isSingleValuedAssociation($propertyName)) {
                    // TODO: Throw exception, this situation should never happen (isn't it?).
                }

                return $classMetadata->getSingleValuedAssociationColumnName($propertyName);
            }
        }

        return ucfirst(parent::normalize($propertyName));
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($propertyName, string $class = null, string $format = null, array $context = [])
    {
        if ($class) {
            /** @var ClassMetadataInterface $classMetadata */
            $classMetadata = $this->registry->getManagerForClass($class)->getClassMetadata($class);

            try {
                return $classMetadata->retrieveField($propertyName);
            } catch (FieldNotFoundException $exception) {
                try {
                    return $classMetadata->retrieveSingleValuedAssociation($propertyName);
                } catch (AssociationNotFoundException $exception) {
                    // Field is not a field nor an association
                }
            }
        }

        return parent::denormalize($propertyName);
    }
}
