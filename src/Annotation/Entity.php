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
    public $repositoryClass;

    /**
     * @var string
     */
    public $connectionClass;

    /**
     * @var string
     */
    public $namespace;
}
