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

namespace NavBundle;

use NavBundle\DependencyInjection\Compiler\EventSubscriberPass;
use NavBundle\DependencyInjection\Compiler\ServiceLocatorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class NavBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new ServiceLocatorPass('nav.entity_listener'));
        $container->addCompilerPass(new ServiceLocatorPass('nav.hydrator'));
        $container->addCompilerPass(new ServiceLocatorPass('nav.entity_repository'));
        $container->addCompilerPass(new EventSubscriberPass());
    }
}
