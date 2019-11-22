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

namespace NavBundle\ClassMetadata\Driver;

use Doctrine\Common\Annotations\Reader;
use NavBundle\Annotation\Column;
use NavBundle\Annotation\Entity;
use NavBundle\Annotation\Id;
use NavBundle\ClassMetadata\ClassMetadataInfo;
use NavBundle\Exception\PathNotFoundException;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class AnnotationClassMetadataDriver implements ClassMetadataDriverInterface
{
    private $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntities(string $path)
    {
        if (!is_dir($path)) {
            throw new PathNotFoundException();
        }

        $iterator = new \RegexIterator(
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::LEAVES_ONLY
            ),
            '/^.+php$/i',
            \RecursiveRegexIterator::GET_MATCH
        );
        $classes = [];
        $declaredClasses = get_declared_classes();
        foreach ($iterator as $file) {
            $sourceFile = realpath($file[0]);
            require_once $sourceFile;
            foreach ($declaredClasses as $class) {
                $rc = new \ReflectionClass($class);
                if ($sourceFile !== $rc->getFileName()) {
                    continue;
                }

                /** @var Entity $entity */
                if ($entity = $this->reader->getClassAnnotation($rc, Entity::class)) {
                    $mapping = [];
                    foreach ($rc->getProperties() as $property) {
                        /** @var Column $column */
                        if ($column = $this->reader->getPropertyAnnotation($property, Column::class)) {
                            $mapping[$property->getName()] = [
                                'name' => $column->name,
                                'identifier' => $this->reader->getPropertyAnnotation($property, Id::class),
                            ];
                        }
                    }

                    $classes[$rc->getName()] = new ClassMetadataInfo($entity->repositoryClass, $entity->namespace, $mapping);
                }
            }
        }

        return $classes;
    }
}
