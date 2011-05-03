<?php

namespace FOS\RestBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder,
    Symfony\Component\Config\Definition\ConfigurationInterface;

use FOS\RestBundle\Response\Codes;

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
            ->fixXmlConfig('normalizer', 'normalizers')
            ->fixXmlConfig('default_normalizer', 'default_normalizers')
            ->fixXmlConfig('class', 'classes')
            ->fixXmlConfig('service', 'services')
            ->children()
                ->arrayNode('formats')
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('default_normalizers')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('normalizers')
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('exception')
                    ->fixXmlConfig('code', 'codes')
                    ->fixXmlConfig('message', 'messages')
                    ->addDefaultsIfNotSet()
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
                    ->fixXmlConfig('default_priority', 'default_priorities')
                    ->treatTrueLike(array('default_priorities' => array('html', '*/*'), 'default_format' => 'html'))
                    ->children()
                        ->arrayNode('default_priorities')
                            ->treatTrueLike(array('html', '*/*'))
                            ->prototype('scalar')->end()
                        ->end()
                        ->scalarNode('default_format')->defaultValue('html')->end()
                    ->end()
                ->end()
                ->booleanNode('body_listener')->defaultFalse()->end()
                ->booleanNode('frameworkextra_bundle')->defaultFalse()->end()
                ->booleanNode('serializer_bundle')->defaultFalse()->end()
                ->scalarNode('failed_validation')->defaultValue(Codes::HTTP_BAD_REQUEST)->end()
                ->arrayNode('classes')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('view')->defaultValue('FOS\RestBundle\View\View')->end()
                        ->scalarNode('serializer')->defaultValue('FOS\RestBundle\Serializer\Serializer')->end()
                        ->scalarNode('json')->defaultValue('Symfony\Component\Serializer\Encoder\JsonEncoder')->end()
                        ->scalarNode('xml')->defaultValue('Symfony\Component\Serializer\Encoder\XmlEncoder')->end()
                        ->scalarNode('html')->defaultValue('FOS\RestBundle\Serializer\Encoder\HtmlEncoder')->end()
                        ->scalarNode('body_listener')->defaultValue('FOS\RestBundle\Request\RequestListener')->end()
                        ->scalarNode('format_listener')->defaultValue('FOS\RestBundle\Controller\ControllerListener')->end()
                    ->end()
                ->end()
                ->arrayNode('services')
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
