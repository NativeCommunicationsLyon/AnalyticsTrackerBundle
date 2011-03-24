<?php

/*
 * This file is part of the JirafePiwikBundle.
 * (c) 2011 Jirafe <http://www.jirafe.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jirafe\Bundle\AnalyticsBundle\DependencyInjection;

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
     * @param Boolean $debug    Wether to use the debug mode
     * @param array   $bundles  An array of bundle names
     *
     * @return \Symfony\Component\Config\Definition\ArrayNode The config tree
     */
    public function getConfigTree($debug, array $bundles)
    {
        $tree = new TreeBuilder();

        $tree->root('jirafe_analytics_tracker')
            ->children()
                ->arrayNode('trackers')
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                        ->scalarNode('type')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->validation()
                                ->rule()
                                    ->ifNotInArray(array('piwik', 'google_analytics', 'jirafe'))
                                    ->thenInvalid('The \'type\' must be either \'piwik\', \'google_analytics\' or \'jirafe\'')
                                ->end()
                            ->end()
                        ->arrayNode('params')
                            ->prototype('array')
                                ->scalarNode('url')->end()
                                ->scalarNode('token')->end()
                                ->scalarNode('site_id')->end()
                                ->scalarNode('account')->end()
                            ->end()
                        ->end()
                        ->scalarNode('class')->end()
                        ->scalarNode('template')->end()
                    ->end()
                ->end()
            ->end();
        ;

        return $tree->buildTree();
    }
}
