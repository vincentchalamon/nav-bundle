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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        if (method_exists(TreeBuilder::class, 'getRootNode')) {
            $treeBuilder = new TreeBuilder('nav');
            $rootNode = $treeBuilder->getRootNode();
        } else {
            $treeBuilder = new TreeBuilder();
            $rootNode = $treeBuilder->root('nav');
        }

        $rootNode
            ->beforeNormalization()
                ->ifTrue(static function ($v) { return \is_string($v['wsdl'] ?? null); })
                ->then(static function ($v) { return ['default' => $v]; })
            ->end()
            ->useAttributeAsKey('name')
            ->arrayPrototype()
                ->children()
                    ->booleanNode('enable_profiler')
                        ->defaultFalse()
                    ->end()
                    ->scalarNode('wsdl')
                        ->info('Microsoft Dynamics NAV WSDL uri.')
                        ->cannotBeEmpty()
                        ->isRequired()
                    ->end()
                    ->scalarNode('path')
                        ->info('Path to the entities.')
                        ->cannotBeEmpty()
                        ->isRequired()
                    ->end()
                    ->enumNode('driver')
                        ->values(['annotation'])
                        ->info('ClassMetadata driver.')
                        ->defaultValue('annotation')
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('domain')
                        ->cannotBeEmpty()
                        ->isRequired()
                    ->end()
                    ->scalarNode('username')
                        ->cannotBeEmpty()
                        ->isRequired()
                    ->end()
                    ->scalarNode('password')
                        ->cannotBeEmpty()
                        ->isRequired()
                    ->end()
                    ->arrayNode('soap_options')
                        ->children()
                            ->booleanNode('cache_wsdl')
                                ->defaultTrue()
                            ->end()
                            ->booleanNode('exception')
                                ->defaultFalse()
                            ->end()
                            ->integerNode('soap_version')
                                ->defaultValue(SOAP_1_1)
                            ->end()
                            ->integerNode('connection_timeout')
                                ->defaultValue(120)
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
