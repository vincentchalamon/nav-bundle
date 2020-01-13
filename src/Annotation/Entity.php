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

use NavBundle\Connection\Connection;
use NavBundle\EntityRepository\EntityRepository;

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
    public $repositoryClass = EntityRepository::class;

    /**
     * @var string
     */
    public $connection = Connection::class;

    /**
     * @var string
     */
    public $namespace;
}
