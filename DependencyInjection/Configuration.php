<?php

namespace FOS\RestBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder,
    Symfony\Component\Config\Definition\ConfigurationInterface;

/*
 * This file is part of the FOSRestBundle
 *
 * (c) Lukas Kahwe Smith <smith@pooteeweet.org>
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 * (c) Bulat Shakirzyanov <mallluhuct@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * This class contains the configuration information for the bundle
 *
 * This information is solely responsible for how the different configuration
 * sections are normalized, and merged.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('fos_rest', 'array');

        $rootNode
            ->fixXmlConfig('format', 'formats')
            ->children()
                ->arrayNode('formats')
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('exception')
                    ->children()
                        ->arrayNode('codes')
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('messages')
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('format_listener')
                    ->treatTrueLike(array('detect_format' => true, 'default_format' => 'html', 'decode_body' => true))
                    ->canBeUnset()
                    ->children()
                        ->scalarNode('detect_format')->defaultFalse()->end()
                        ->scalarNode('default_format')->defaultValue('html')->end()
                        ->scalarNode('decode_body')->defaultFalse()->end()
                    ->end()
                ->end()
                ->booleanNode('frameworkextra')->defaultFalse()->end()
                ->arrayNode('class')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('view')->defaultValue('FOS\RestBundle\View\View')->end()
                        ->scalarNode('serializer')->defaultValue('Symfony\Component\Serializer\Serializer')->end()
                        ->scalarNode('json')->defaultValue('Symfony\Component\Serializer\Encoder\JsonEncoder')->end()
                        ->scalarNode('xml')->defaultValue('Symfony\Component\Serializer\Encoder\XmlEncoder')->end()
                        ->scalarNode('html')->defaultValue('FOS\RestBundle\Serializer\Encoder\HtmlEncoder')->end()
                        ->scalarNode('request_format_listener')->defaultValue('FOS\RestBundle\Request\RequestListener')->end()
                    ->end()
                ->end()
                ->arrayNode('service')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('view')->defaultNull()->end()
                        ->scalarNode('serializer')->defaultNull()->end()
                        ->scalarNode('json')->defaultNull()->end()
                        ->scalarNode('xml')->defaultNull()->end()
                        ->scalarNode('html')->defaultNull()->end()
                        ->scalarNode('request_format_listener')->defaultNull()->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }

}
