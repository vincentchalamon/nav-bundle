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

namespace NavBundle\ClassMetadata;

use NavBundle\Exception\EntityNotFoundException;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
interface ClassMetadataInterface
{
    /**
     * Get the ClassMetadataInfo for a class.
     *
     * @throws EntityNotFoundException
     *
     * @return ClassMetadataInfoInterface the ClassMetadataInfo
     */
    public function getClassMetadataInfo(string $class): ClassMetadataInfoInterface;

    /**
     * Get all the ClassMetadataInfo.
     *
     * @return ClassMetadataInfoInterface[] a collection of ClassMetadataInfo
     */
    public function getClassMetadataInfos();
}
