<?php

declare(strict_types=1);

namespace NavBundle\Annotation;

use NavBundle\Exception\InvalidArgumentException;
use NavBundle\Repository\NavRepository;

/**
 * @Annotation
 * @Target("CLASS")
 *
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
final class NavEntity
{
    /**
     * @var string
     */
    public $repositoryClass = NavRepository::class;

    /**
     * @var string
     */
    public $namespace;

    public function __construct(array $data)
    {
        if (!isset($data['value']) || !$data['value']) {
            throw new InvalidArgumentException(sprintf('Parameter of annotation "%s" cannot be empty.', \get_class($this)));
        }

        $this->namespace = (string) $data['value'];
    }
}
