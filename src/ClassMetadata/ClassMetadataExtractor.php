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
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\PropertyInfo\Type;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class ClassMetadataExtractor implements PropertyTypeExtractorInterface
{
    private $registry;

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypes($className, $property, array $context = [])
    {
        try {
            $mapping = $this->registry->getManagerForClass($className)->getClassMetadata($className)->getMapping()[$property];
            $class = null;
            if (\in_array(strtolower($mapping['type']), ['date', 'datetime'], true)) {
                $mapping['type'] = Type::BUILTIN_TYPE_OBJECT;
                $class = \DateTime::class;
            }

            return [new Type($mapping['type'], $mapping['nullable'], $class)];
        } catch (ExceptionInterface $exception) {
            return null;
        }
    }
}
