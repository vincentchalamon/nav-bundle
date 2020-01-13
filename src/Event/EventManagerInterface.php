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

namespace NavBundle\Event;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
interface EventManagerInterface
{
    /**
     * Dispatches an event to all registered listeners.
     *
     * @param EventInterface $event the event to pass to the event handlers/listeners
     */
    public function dispatch(EventInterface $event): void;
}
