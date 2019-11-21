<?php

declare(strict_types=1);

namespace NavBundle\ClassMetadata\Driver;

use Doctrine\Common\Annotations\Reader;
use NavBundle\Annotation\NavEntity;
use NavBundle\ClassMetadata\ClassMetadataInfo;
use NavBundle\Exception\PathNotFoundException;

/**
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class AnnotationClassMetadataDriver implements ClassMetadataDriverInterface
{
    private $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * {@inheritDoc}
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
        foreach ($iterator as $file) {
            $rc = new \ReflectionClass(realpath($file[0]));
            /** @var NavEntity $annotation */
            if ($annotation = $this->reader->getClassAnnotation($rc, NavEntity::class)) {
                $classes[$rc->getName()] = new ClassMetadataInfo($annotation->repositoryClass, $annotation->namespace);
            }
        }

        return $classes;
    }
}
