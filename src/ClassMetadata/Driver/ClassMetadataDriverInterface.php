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

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
interface ClassMetadataDriverInterface
{
    /**
     * Get entities class name and options.
     *
     * @return array the entities class name and options
     */
    public function getEntities();
}
