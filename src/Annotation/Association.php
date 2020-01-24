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

use NavBundle\ClassMetadata\ClassMetadata;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
abstract class Association
{
    /**
     * @var string
     */
    public $targetClass;

    /**
     * @var string
     */
    public $fetch = ClassMetadata::FETCH_LAZY;

    /**
     * @var array<string>
     */
    public $cascade;
}
