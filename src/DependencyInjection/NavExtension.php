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
use NavBundle\ClassMetadata\ClassMetadata;
use NavBundle\Debug\DataCollector\NavDataCollector;
use NavBundle\Debug\Manager\TraceableManager;
use NavBundle\Manager\Manager;
use NavBundle\Manager\ManagerInterface;
use NavBundle\Repository\RepositoryInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
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

        foreach ($config as $name => $options) {
            $container->register("nav.class_metadata.$name", ClassMetadata::class)
                ->setPublic(false)
                ->setArguments([
                    '$driver' => new Reference("nav.class_metadata.driver.$options[driver]"),
                    '$path' => $options['path'],
                ]);

            $container->register("nav.manager.$name", Manager::class)
                ->setPublic(true)
                ->addTag('nav.manager', ['name' => $name])
                ->setArguments([
                    '$classMetadata' => new Reference("nav.class_metadata.$name"),
                    '$serializer' => new Reference('serializer'),
                    '$propertyAccessor' => new Reference('property_accessor'),
                    '$repositories' => new TaggedIteratorArgument('nav.repository'), // todo Inject related repositories
                    '$clients' => new TaggedIteratorArgument('nav.client'), // todo Inject related clients
                    '$wsdl' => $options['wsdl'],
                    '$options' => [
                        'username' => $options['username'],
                        'password' => $options['password'],
                        'domain' => $options['domain'],
                    ],
                    '$soapOptions' => $options['soap_options'] + ['trace' => $options['enable_profiler']],
                ]);
            if (!$container->hasAlias('nav.manager')) {
                $container->setAlias('nav.manager', new Alias("nav.manager.$name"));
                $container->setAlias(Manager::class, new Alias("nav.manager.$name"));
                $container->setAlias(ManagerInterface::class, new Alias("nav.manager.$name"));
            }

            if ($options['enable_profiler']) {
                $container->getDefinition("nav.manager.$name")
                    ->setClass(TraceableManager::class)
                    ->setArgument('$stopwatch', new Reference('debug.stopwatch'));
                $container->register('nav.data_collector', NavDataCollector::class)
                    ->setArguments([
                        '$managers' => new TaggedIteratorArgument('nav.manager', 'name'),
                    ])
                    ->addTag('data_collector', [
                        'template' => '@Nav/Collector/nav.html.twig',
                        'id' => 'nav',
                    ]);
            }
        }

        $container
            ->getDefinition('nav.registry')
            ->setArgument('$managers', new TaggedIteratorArgument('nav.manager'));
    }
}
