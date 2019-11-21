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
 * @author Vincent Chalamon <vincent@les-tilleuls.coop>
 */
interface ClassMetadataInterface
{
    /**
     * Get the ClassMetadataInfo for a class.
     *
     * @return ClassMetadataInfoInterface The ClassMetadataInfo.
     *
     * @throws EntityNotFoundException
     */
    public function getClassMetadataInfo(string $class): ClassMetadataInfoInterface;

    /**
     * Get all the ClassMetadataInfo.
     *
     * @return ClassMetadataInfoInterface[] A collection of ClassMetadataInfo.
     */
    public function getClassMetadataInfos();
}
