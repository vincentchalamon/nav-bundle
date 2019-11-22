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

use matejsvajger\NTLMSoap\Model\BaseClient;
use NavBundle\Debug\Manager\TraceableManager;
use NavBundle\Manager\Manager;
use NavBundle\Manager\ManagerInterface;
use NavBundle\Repository\RepositoryInterface;
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
            ->registerForAutoconfiguration(BaseClient::class)
            ->addTag('nav.client');

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

            if ($config['enable_profiler']) {
                $options['soap_options']['trace'] = true;
            }
            $container
                ->setDefinition("nav.manager.$name", new ChildDefinition('nav.abstract_manager'))
                ->setPublic(true)
                ->addTag('nav.manager', ['name' => $name])
                ->setArgument('$driver', new Reference("nav.class_metadata_driver.$name"))
                ->setArgument('$wsdl', $options['wsdl'])
                ->setArgument('$options', [
                    'username' => $options['username'],
                    'password' => $options['password'],
                    'domain' => $options['domain'],
                ])
                ->setArgument('$soapOptions', $options['soap_options']);
            if (!$container->hasAlias('nav.manager')) {
                $container->setAlias('nav.manager', new Alias("nav.manager.$name"));
                $container->setAlias(Manager::class, new Alias("nav.manager.$name"));
                $container->setAlias(ManagerInterface::class, new Alias("nav.manager.$name"));
            }

            if ($config['enable_profiler']) {
                $container
                    ->getDefinition("nav.manager.$name")
                    ->setClass(TraceableManager::class)
                    ->setArgument('$stopwatch', new Reference('debug.stopwatch'));
            }
        }

        $container
            ->getDefinition('nav.registry')
            ->setArgument('$managers', new TaggedIteratorArgument('nav.manager', 'name'));
    }
}
