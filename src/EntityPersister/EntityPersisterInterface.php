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

namespace NavBundle\EntityPersister;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
interface EntityPersisterInterface
{
    /**
     * Loads a list of entities by a list of field criteria.
     *
     * @param int|null    $limit
     * @param string|null $offset
     *
     * @return \Iterator|array<object>
     */
    public function loadAll(array $criteria = [], $limit = null, $offset = null);

    /**
     * Loads an entity by a list of field criteria.
     *
     * @param array $criteria the criteria by which to load the entity
     *
     * @return object|null
     */
    public function load(array $criteria);
}
