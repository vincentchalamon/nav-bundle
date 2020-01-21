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

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventManager as DoctrineEventManager;
use NavBundle\ClassMetadata\ClassMetadataInterface;
use NavBundle\EntityListener\EntityListenerResolverInterface;
use NavBundle\Util\ClassUtils;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class EventManager extends DoctrineEventManager implements EventManagerInterface
{
    private $entityListenerResolver;

    public function __construct(EntityListenerResolverInterface $entityListenerResolver, iterable $eventSubscribers)
    {
        $this->entityListenerResolver = $entityListenerResolver;
        foreach ($eventSubscribers as $eventSubscriber) {
            $this->addEventSubscriber($eventSubscriber);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(EventInterface $eventArgs): void
    {
        // Call entity listeners
        /** @var ClassMetadataInterface $classMetadata */
        $object = $eventArgs->getObject();
        $classMetadata = $eventArgs->getObjectManager()->getClassMetadata(ClassUtils::getRealClass($object));
        foreach ($classMetadata->getEntityListeners() as $entityListener) {
            \call_user_func([$this->entityListenerResolver->resolve($entityListener), $eventArgs->getName()], $object, $eventArgs);
        }

        // Call event listeners/subscribers
        /* @var EventArgs $eventArgs */
        parent::dispatchEvent($eventArgs->getName(), $eventArgs);
    }
}
