<?php

declare(strict_types=1);

namespace NavBundle\Serializer\NameConverter;

use NavBundle\Exception\FieldNotFoundException;
use NavBundle\RegistryInterface;
use Symfony\Component\Serializer\NameConverter\AdvancedNameConverterInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
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
     * {@inheritDoc}
     */
    public function normalize($propertyName, string $class = null, string $format = null, array $context = [])
    {
        if (!$class) {
            return parent::normalize($propertyName);
        }

        try {
            return $this->registry->getManagerForClass($class)->getClassMetadata($class)->getFieldColumnName($propertyName);
        } catch (FieldNotFoundException $exception) {
            return parent::normalize($propertyName);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function denormalize($propertyName, string $class = null, string $format = null, array $context = [])
    {
        if (!$class) {
            return parent::denormalize($propertyName);
        }

        try {
            return $this->registry->getManagerForClass($class)->getClassMetadata($class)->retrieveField($propertyName);
        } catch (FieldNotFoundException $exception) {
            return parent::denormalize($propertyName);
        }
    }
}