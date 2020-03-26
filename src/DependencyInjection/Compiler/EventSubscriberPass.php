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

namespace NavBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 *
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class EventSubscriberPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $eventManagerDefinition = $container->getDefinition('nav.event_manager');

        foreach ($this->findAndSortTaggedServices('nav.event_subscriber', $container) as $reference) {
            $eventManagerDefinition->addMethodCall('addEventSubscriber', [$reference]);
        }
    }
}
