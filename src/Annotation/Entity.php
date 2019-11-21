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

namespace NavBundle\Annotation;

use NavBundle\Exception\InvalidArgumentException;
use NavBundle\Repository\Repository;

/**
 * @Annotation
 * @Target("CLASS")
 *
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class Entity
{
    /**
     * @var string
     */
    public $repositoryClass = Repository::class;

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
