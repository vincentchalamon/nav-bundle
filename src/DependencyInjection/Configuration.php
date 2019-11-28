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
        $treeBuilder = new TreeBuilder('nav');
        $treeBuilder
            ->getRootNode()
            ->beforeNormalization()
                ->ifTrue(static function ($v) { return \is_string($v['wsdl'] ?? null); })
                ->then(static function ($v) {
                    $debug = $v['enable_profiler'];
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
                            ->scalarNode('username')
                                ->cannotBeEmpty()
                                ->isRequired()
                            ->end()
                            ->scalarNode('password')
                                ->cannotBeEmpty()
                                ->isRequired()
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
