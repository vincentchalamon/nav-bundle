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

/**
 * @Annotation
 * @Target("PROPERTY")
 *
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class Column
{
    /**
     * @var string
     */
    public $name;

    public function __construct(array $data)
    {
        if (!isset($data['value']) || !$data['value']) {
            throw new InvalidArgumentException(sprintf('Parameter of annotation "%s" cannot be empty.', \get_class($this)));
        }

        $this->name = (string) $data['value'];
    }
}
