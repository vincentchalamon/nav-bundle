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

namespace NavBundle\NamingStrategy;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
class DefaultNamingStrategy implements NamingStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function propertyToColumnName($propertyName, $className = null)
    {
        return ucfirst(preg_replace('/([a-z])([A-Z])/', '$1_$2', $propertyName));
    }
}
