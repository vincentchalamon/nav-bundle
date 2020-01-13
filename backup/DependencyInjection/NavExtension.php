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

namespace Backup\NavBundle\DependencyInjection;

use Backup\NavBundle\Bridge\ApiPlatform\DataProvider\CollectionExtensionInterface;
use Backup\NavBundle\Bridge\ApiPlatform\DataProvider\ItemExtensionInterface;
use Backup\NavBundle\Debug\Manager\TraceableManager;
use Backup\NavBundle\EntityManager\EntityManager;
use Backup\NavBundle\EntityManager\EntityManagerInterface;
use Backup\NavBundle\EntityRepository\ServiceEntityRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

final class NavExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container
            ->registerForAutoconfiguration(ItemExtensionInterface::class)
            ->addTag('nav.item_extension');
        $container
            ->registerForAutoconfiguration(CollectionExtensionInterface::class)
            ->addTag('nav.collection_extension');
    }
}
