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
use NavBundle\Annotation\Key;
use NavBundle\Annotation\No;
use NavBundle\Exception\PathNotFoundException;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class AnnotationClassMetadataDriver implements ClassMetadataDriverInterface
{
    private $reader;
    private $path;

    public function __construct(Reader $reader, string $path)
    {
        $this->reader = $reader;
        $this->path = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntities()
    {
        if (!is_dir($this->path)) {
            throw new PathNotFoundException();
        }

        $iterator = new \RegexIterator(
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->path, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::LEAVES_ONLY
            ),
            '/^.+php$/i',
            \RecursiveRegexIterator::GET_MATCH
        );

        $includedFiles = [];
        foreach ($iterator as $file) {
            $sourceFile = $file[0];

            if (!preg_match('(^phar:)i', $sourceFile)) {
                $sourceFile = realpath($sourceFile);
            }

            require_once $sourceFile;

            $includedFiles[] = $sourceFile;
        }

        $classes = [];
        $declaredClasses = get_declared_classes();
        foreach ($declaredClasses as $className) {
            $rc = new \ReflectionClass($className);
            $sourceFile = $rc->getFileName();

            /** @var Entity $entity */
            if (!\in_array($sourceFile, $includedFiles, true) || !($entity = $this->reader->getClassAnnotation($rc, Entity::class))) {
                continue;
            }

            $mapping = [];
            foreach ($rc->getProperties() as $property) {
                /** @var Column $column */
                if (!($column = $this->reader->getPropertyAnnotation($property, Column::class))) {
                    continue;
                }

                $mapping[$property->getName()] = [
                    'name' => $column->name,
                    'type' => $column->type,
                    'nullable' => $column->nullable,
                    'no' => (bool) $this->reader->getPropertyAnnotation($property, No::class),
                    'key' => (bool) $this->reader->getPropertyAnnotation($property, Key::class),
                ];
            }

            $classes[$rc->getName()] = [
                'repositoryClass' => $entity->repositoryClass,
                'namespace' => $entity->namespace,
                'mapping' => $mapping,
            ];
        }

        return $classes;
    }
}
