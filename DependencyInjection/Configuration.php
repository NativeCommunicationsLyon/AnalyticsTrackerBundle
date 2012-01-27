<?php

/*
 * This file is part of the AnalyticsTrackerBundle.
 * (c) 2011 Jirafe <http://www.jirafe.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jirafe\Bundle\AnalyticsTrackerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * This class contains the configuration information for the bundle
 *
 * This information is solely responsible for how the different configuration
 * sections are normalized, and merged.
 */
class Configuration
{
    /**
     * Generates the configuration tree.
     *
     * @return \Symfony\Component\Config\Definition\ArrayNode The config tree
     */
    public function getConfigTree()
    {
        $tree = new TreeBuilder();
        $rootNode = $tree->root('jirafe_analytics_tracker');

        $rootNode
            ->children()
            ->arrayNode('trackers')
                ->requiresAtLeastOneElement()
                ->useAttributeAsKey('name')
                ->prototype('array')
                    ->children()
                        ->scalarNode('type')->end()
                        ->scalarNode('class')->defaultNull()->end()
                        ->scalarNode('template')->defaultNull()->end()
                        ->arrayNode('params')
                            ->children()
                                ->scalarNode('url')->end()
                                ->scalarNode('site_id')->end()
                                ->scalarNode('account')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $tree->buildTree();
    }
}
