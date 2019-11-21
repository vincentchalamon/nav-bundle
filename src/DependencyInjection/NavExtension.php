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

namespace NavBundle\DependencyInjection;

use NavBundle\Manager\NavManager;
use NavBundle\Manager\NavManagerInterface;
use NavBundle\Repository\NavRepositoryInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

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
            ->registerForAutoconfiguration(NavRepositoryInterface::class)
            ->addTag('nav.repository');

        $default = null;
        foreach ($config as $managerName => $options) {
            $container->register("nav.manager.$managerName", NavManager::class)
                ->setPublic(false)
                ->setArguments([]);

            if (null === $default) {
                $container->setAlias(NavManager::class, new Alias("nav.manager.$managerName"));
                $container->setAlias(NavManagerInterface::class, new Alias("nav.manager.$managerName"));
                $container->setAlias('nav.manager', new Alias("nav.manager.$managerName"));
            }
        }

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container
            ->getDefinition('nav.registry')
            ->setArgument('$wsdl', $config['wsdl'])
            ->setArgument('$defaultOptions', $config['options']);
    }
}
