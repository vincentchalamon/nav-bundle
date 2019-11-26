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

namespace NavBundle\ClassMetadata;

use NavBundle\Exception\ExceptionInterface;
use NavBundle\RegistryInterface;
use NavBundle\Type\TypeFactory;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\PropertyInfo\Type;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ClassMetadataExtractor implements PropertyTypeExtractorInterface
{
    private $registry;
    private $typeFactory;

    public function __construct(RegistryInterface $registry, TypeFactory $type)
    {
        $this->registry = $registry;
        $this->typeFactory = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypes($className, $property, array $context = []): ?array
    {
        try {
            $mapping = $this->registry->getManagerForClass($className)->getClassMetadata($className)->getMapping()[$property];
            $type = $this->typeFactory->getType($mapping['type']);

            return [new Type($type->getBuiltInType(), $mapping['nullable'], $type->getClass())];
        } catch (ExceptionInterface $exception) {
            return null;
        }
    }
}
