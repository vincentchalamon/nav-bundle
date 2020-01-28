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
 * @Target("PROPERTY")
 *
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class OneToOne extends Association
{
    /**
     * @var string
     */
    public $columnName;

    /**
     * @var string
     */
    public $inversedBy;

    /**
     * @var bool
     */
    public $nullable = true;

    /**
     * @var string
     */
    public $mappedBy;
}
