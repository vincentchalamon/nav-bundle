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

use NavBundle\ClassMetadata\ClassMetadataInfoInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
interface ClassMetadataDriverInterface
{
    /**
     * Get entities class name and options.
     *
     * @param string $path the path where the entities configuration is stored
     *
     * @return ClassMetadataInfoInterface[] the entities class name and options
     */
    public function getEntities(string $path);
}
