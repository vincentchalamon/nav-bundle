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

use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
/*final */class PostLoadEvent extends LifecycleEventArgs implements EventInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'postLoad';
    }
}
