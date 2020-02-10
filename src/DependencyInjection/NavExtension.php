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

use ApiPlatform\Core\Bridge\Symfony\Bundle\ApiPlatformBundle;
use EasyCorp\Bundle\EasyAdminBundle\EasyAdminBundle;
use NavBundle\Debug\Connection\TraceableConnectionResolver;
use NavBundle\EntityManager\EntityManager;
use NavBundle\EntityManager\EntityManagerInterface;
use NavBundle\EntityRepository\ServiceEntityRepositoryInterface;
use NavBundle\Event\EventSubscriberInterface;
use NavBundle\Exception\DriverNotFoundException;
use NavBundle\Hydrator\HydratorInterface;
use NavBundle\PropertyInfo\NavExtractor;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

final class NavExtension extends Extension
{
    private const URL_PATTERN = '#^(https?)://([^:]+):([^@]+)@(.*)$#';

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container
            ->registerForAutoconfiguration(ServiceEntityRepositoryInterface::class)
            ->addTag('nav.entity_repository');
        $container
            ->registerForAutoconfiguration(EventSubscriberInterface::class)
            ->addTag('nav.event_subscriber');
        $container
            ->registerForAutoconfiguration(HydratorInterface::class)
            ->addTag('nav.hydrator');

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        if ($config['enable_profiler']) {
            $loader->load('debug.xml');
        }

        $bundles = $container->getParameter('kernel.bundles');
        if (\in_array(ApiPlatformBundle::class, $bundles, true)) {
            $loader->load('api_platform.xml');
        }

        if (\in_array(SensioFrameworkExtraBundle::class, $bundles, true)) {
            $loader->load('sensio_framework_extra.xml');
        }

        if (\in_array(EasyAdminBundle::class, $bundles, true)) {
            $loader->load('easy_admin.xml');
        }

        $managers = [];
        foreach ($config['managers'] as $name => $options) {
            if (!$container->hasDefinition($options['driver'])) {
                throw new DriverNotFoundException();
            }

            // Parse url
            if (!empty($options['url'])) {
                $options['url'] = $container->resolveEnvPlaceholders($options['url'], true);
                if (!preg_match(self::URL_PATTERN, $options['url'], $matches)) {
                    throw new \InvalidArgumentException('Malformed parameter "url".');
                }

                $options['wsdl'] = $matches[1].'://'.$matches[4];
                $options['connection']['username'] = $matches[2];
                $options['connection']['password'] = $matches[3];
                unset($options['url']);
            } else {
                $options['wsdl'] = $container->resolveEnvPlaceholders($options['wsdl'], true);
                $options['connection']['username'] = $container->resolveEnvPlaceholders($options['connection']['username'], true);
                $options['connection']['password'] = $container->resolveEnvPlaceholders($options['connection']['password'], true);
            }

            // Configure connection resolver
            $container
                ->setDefinition("nav.connection_resolver.$name", new ChildDefinition('nav.connection_resolver.abstract'))
                ->setPublic(true)
                ->setArgument('$className', $options['connection']['class'])
                ->setArgument('$wsdl', $options['wsdl'])
                ->setArgument('$options', [
                    'user' => $options['connection']['username'],
                    'password' => $options['connection']['password'],
                    'cache_dir' => '%kernel.cache_dir%/nav/WSDL',
                ] + $options['soap_options']
                );

            // Configure driver
            $container
                ->setDefinition("nav.entity_manager.$name.driver", new ChildDefinition($options['driver']))
                ->setPublic(false)
                ->setArgument('$paths', array_values(array_map(function (array $path) {
                    return $path['path'];
                }, $options['paths'])));

            // Configure PropertyInfo extractor
            $container
                ->setDefinition("nav.entity_manager.$name.property_info_extractor", new Definition(NavExtractor::class))
                ->setPublic(false)
                ->setArgument('$entityManager', new Reference("nav.entity_manager.$name"))
                ->addTag('property_info.list_extractor')
                ->addTag('property_info.type_extractor')
                ->addTag('property_info.access_extractor');

            // Configure entity manager
            $container
                ->setDefinition("nav.entity_manager.$name", new ChildDefinition('nav.entity_manager.abstract'))
                ->setClass($options['entity_manager_class'])
                ->setPublic(true)
                ->setArgument('$connectionResolver', new Reference("nav.connection_resolver.$name"))
                ->setArgument('$mappingDriver', new Reference("nav.entity_manager.$name.driver"))
                ->setArgument('$nameConverter', new Reference($options['name_converter']))
                ->setArgument('$entityNamespaces', array_map(function (array $path) {
                    return $path['namespace'];
                }, $options['paths']));
            $managers[$name] = "nav.entity_manager.$name";

            if (!$container->hasAlias('nav.entity_manager')) {
                $container->setAlias('nav.entity_manager', new Alias("nav.entity_manager.$name"));
                $container->setAlias(EntityManager::class, new Alias("nav.entity_manager.$name"));
                $container->setAlias(EntityManagerInterface::class, new Alias("nav.entity_manager.$name"));

                $container
                    ->getDefinition('nav.registry')
                    ->setArgument('$defaultManagerName', $name);
            }

            if ($config['enable_profiler']) {
                $container
                    ->setDefinition("nav.connection_resolver.$name.traceable", new Definition(TraceableConnectionResolver::class))
                    ->setDecoratedService("nav.connection_resolver.$name", null, 100)
                    ->setArgument('$decorated', new Reference("nav.connection_resolver.$name.traceable.inner"))
                    ->setArgument('$stopwatch', new Reference('debug.stopwatch'));
            }
        }

        $container
            ->getDefinition('nav.registry')
            ->setArgument('$managers', $managers);
    }
}
