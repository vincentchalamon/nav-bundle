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

use NavBundle\Debug\Manager\TraceableManager;
use NavBundle\Manager\Manager;
use NavBundle\Manager\ManagerInterface;
use NavBundle\Repository\RepositoryInterface;
use NavBundle\Type\TypeInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

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
            ->registerForAutoconfiguration(RepositoryInterface::class)
            ->addTag('nav.repository');
        $container
            ->registerForAutoconfiguration(ManagerInterface::class)
            ->addTag('nav.manager');
        $container
            ->registerForAutoconfiguration(TypeInterface::class)
            ->addTag('nav.type');

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
        if ($config['enable_profiler']) {
            $loader->load('debug.xml');
            $container
                ->getDefinition('nav.data_collector')
                ->setArgument('$managers', new TaggedIteratorArgument('nav.manager', 'name'));
        }

        foreach ($config['managers'] as $name => $options) {
            $container
                ->setDefinition("nav.class_metadata_driver.$name", new ChildDefinition("nav.class_metadata.driver.$options[driver]"))
                ->setArgument('$path', $options['path'])
                ->setPublic(false);

            $container
                ->setDefinition("nav.manager.$name", new ChildDefinition('nav.abstract_manager'))
                ->setPublic(true)
                ->addTag('nav.manager', ['name' => $name])
                ->setArgument('$driver', new Reference("nav.class_metadata_driver.$name"))
                ->setArgument('$wsdl', $options['wsdl'])
                ->setArgument('$soapOptions', [
                    'user' => $options['username'],
                    'password' => $options['password'],
                    'cache_dir' => '%kernel.cache_dir%/nav',
                ] + $options['soap_options'] + [
                    'cache_wsdl' => WSDL_CACHE_NONE,
                    'exception' => true,
                    'soap_version' => SOAP_1_1,
                    'connection_timeout' => 120,
                ]);
            if (!$container->hasAlias('nav.manager')) {
                $container->setAlias('nav.manager', new Alias("nav.manager.$name"));
                $container->setAlias(Manager::class, new Alias("nav.manager.$name"));
                $container->setAlias(ManagerInterface::class, new Alias("nav.manager.$name"));
            }

            if ($config['enable_profiler']) {
                $container
                    ->getDefinition("nav.manager.$name")
                    ->setClass(TraceableManager::class)
                    ->addMethodCall('setStopwatch', [new Reference('debug.stopwatch')]);
            }
        }

        $container
            ->getDefinition('nav.registry')
            ->setArgument('$managers', new TaggedIteratorArgument('nav.manager', 'name'));
    }
}
