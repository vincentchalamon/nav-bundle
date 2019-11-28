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

use NavBundle\Exception\KeyNotFoundException;
use NavBundle\Exception\NoNotFoundException;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
interface ClassMetadataInterface
{
    /**
     * Get the repository class name.
     *
     * @return string the repository class name
     */
    public function getRepositoryClass(): string;

    /**
     * Get the entity NAV namespace.
     *
     * @return string the entity NAV namespace
     */
    public function getNamespace(): string;

    /**
     * Get the entity mapping.
     *
     * @return array the entity mapping
     */
    public function getMapping(): array;

    /**
     * Get the entity 'No' property name.
     *
     * @throws NoNotFoundException
     *
     * @return string the property name
     */
    public function getNo(): string;

    /**
     * Get the entity 'Key' property name.
     *
     * @throws KeyNotFoundException
     *
     * @return string the property name
     */
    public function getKey(): string;
}
