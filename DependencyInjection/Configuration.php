<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder,
    Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition,
    Symfony\Component\Config\Definition\ConfigurationInterface;

use FOS\RestBundle\Response\Codes;

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
            ->fixXmlConfig('templating_format', 'templating_formats')
            ->fixXmlConfig('force_redirect', 'force_redirects')
            ->children()
                ->arrayNode('formats')
                    ->defaultValue(array('json', 'xml'))
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('templating_formats')
                    ->defaultValue(array('html'))
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('force_redirects')
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('routing_loader')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('default_format')->defaultNull()->end()
                    ->end()
                ->end()
                ->booleanNode('view_response_listener')->defaultTrue()->end()
                ->scalarNode('failed_validation')->defaultValue(Codes::HTTP_BAD_REQUEST)->end()
                ->arrayNode('service')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('view_handler')->defaultValue('fos_rest.view_handler.default')->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        $this->addExceptionSection($rootNode);
        $this->addBodyListenerSection($rootNode);
        $this->addFormatListenerSection($rootNode);
        $this->addFlashMessageListenerSection($rootNode);
        
        return $treeBuilder;
    }

    private function addBodyListenerSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('body_listener')
                    ->fixXmlConfig('decoder', 'decoders')
                    ->addDefaultsIfNotSet()
                    ->canBeUnset()
                    ->children()
                        ->arrayNode('decoders')
                            ->useAttributeAsKey('name')
                            ->defaultValue(array('json' => 'fos_rest.decoder.json', 'xml' => 'fos_rest.decoder.xml'))
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addFormatListenerSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('format_listener')
                    ->fixXmlConfig('default_priority', 'default_priorities')
                    ->addDefaultsIfNotSet()
                    ->canBeUnset()
                    ->children()
                        ->arrayNode('default_priorities')
                            ->defaultValue(array('html', '*/*'))
                            ->prototype('scalar')->end()
                        ->end()
                        ->scalarNode('fallback_format')->defaultValue('html')->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addFlashMessageListenerSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('flash_message_listener')
                    ->addDefaultsIfNotSet()
                    ->canBeUnset()
                    ->children()
                        ->scalarNode('name')->defaultValue('flashes')->end()
                        ->scalarNode('path')->defaultValue('/')->end()
                        ->scalarNode('domain')->defaultNull()->end()
                        ->scalarNode('secure')->defaultFalse()->end()
                        ->scalarNode('httpOnly')->defaultTrue()->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addExceptionSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
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
            ->end();
    }
}
