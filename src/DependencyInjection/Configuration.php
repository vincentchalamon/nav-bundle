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

use NavBundle\Connection\Connection;
use NavBundle\EntityManager\EntityManager;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('nav');
        $treeBuilder
            ->getRootNode()
            ->beforeNormalization()
                ->ifTrue(static function ($v): bool { return \is_string($v['url'] ?? null); })
                ->then(static function ($v): array {
                    $debug = $v['enable_profiler'] ?? false;
                    unset($v['enable_profiler']);

                    return [
                        'enable_profiler' => $debug,
                        'managers' => ['default' => $v],
                    ];
                })
            ->end()
            ->children()
                ->booleanNode('enable_profiler')
                    ->defaultFalse()
                ->end()
                ->arrayNode('managers')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('url')
                                ->info('Microsoft Dynamics NAV WSDL uri with credentials.')
                                ->cannotBeEmpty()
                                ->isRequired()
                            ->end()
                            ->arrayNode('paths')
                                ->useAttributeAsKey('alias')
                                ->info('Paths to the entities.')
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('path')
                                            ->info('Directory where the entity files are stored.')
                                            ->cannotBeEmpty()
                                            ->isRequired()
                                        ->end()
                                        ->scalarNode('namespace')
                                            ->info('Namespace of the entities.')
                                            ->cannotBeEmpty()
                                            ->isRequired()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->scalarNode('entity_manager_class')
                                ->info('Entity manager class.')
                                ->defaultValue(EntityManager::class)
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('connection_class')
                                ->info('Connection class.')
                                ->defaultValue(Connection::class)
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('driver')
                                ->info('ClassMetadata driver.')
                                ->defaultValue('nav.class_metadata.driver.annotation')
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('name_converter')
                                ->info('Name converter (any service instance of NameConverterInterver).')
                                ->defaultValue('nav.serializer.name_converter.camel_case_to_nav')
                                ->cannotBeEmpty()
                            ->end()
                            ->arrayNode('soap_options')
                                ->useAttributeAsKey('name')
                                ->scalarPrototype()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
