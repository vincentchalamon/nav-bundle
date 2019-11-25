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

use NavBundle\Manager\ManagerInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
abstract class EntityEvent extends Event
{
    protected $manager;
    protected $entity;

    public function __construct(ManagerInterface $manager, object $entity)
    {
        $this->manager = $manager;
        $this->entity = $entity;
    }

    public function getManager(): ManagerInterface
    {
        return $this->manager;
    }

    public function getEntity(): object
    {
        return $this->entity;
    }
}
