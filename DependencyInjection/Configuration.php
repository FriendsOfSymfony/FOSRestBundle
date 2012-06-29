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

use FOS\Rest\Util\Codes;

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
    private $forceOptionValues = array(false, true, 'force');

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
            ->children()
                ->scalarNode('param_fetcher_listener')->defaultFalse()
                    ->validate()
                        ->ifNotInArray($this->forceOptionValues)
                        ->thenInvalid('The param_fetcher_listener option does not support %s. Please choose one of '.json_encode($this->forceOptionValues))
                    ->end()
                ->end()
                ->arrayNode('routing_loader')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('default_format')->defaultNull()->end()
                    ->end()
                ->end()
                ->arrayNode('service')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('router')->defaultValue('router')->end()
                        ->scalarNode('templating')->defaultValue('templating')->end()
                        ->scalarNode('serializer')->defaultValue('jms_serializer.serializer')->end()
                        ->scalarNode('view_handler')->defaultValue('fos_rest.view_handler.default')->end()
                    ->end()
                ->end()
                ->arrayNode('serializer')
                    ->validate()
                        ->ifTrue(function($v) { return !empty($v['version']) && !empty($v['groups']); })
                        ->thenInvalid('Only either a version or a groups exclusion strategy can be set')
                    ->end()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('version')->defaultNull()->end()
                        ->arrayNode('groups')
                            ->defaultValue(array())
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        $this->addViewSection($rootNode);
        $this->addExceptionSection($rootNode);
        $this->addBodyListenerSection($rootNode);
        $this->addFormatListenerSection($rootNode);

        return $treeBuilder;
    }

    private function addViewSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('view')
                    ->fixXmlConfig('format', 'formats')
                    ->fixXmlConfig('mime_type', 'mime_types')
                    ->fixXmlConfig('templating_format', 'templating_formats')
                    ->fixXmlConfig('force_redirect', 'force_redirects')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('default_engine')->defaultValue('twig')->end()
                        ->arrayNode('force_redirects')
                            ->useAttributeAsKey('name')
                            ->defaultValue(array('html' => true))
                            ->prototype('boolean')->end()
                        ->end()
                        ->arrayNode('mime_types')
                            ->useAttributeAsKey('name')
                            ->defaultValue(array())
                            ->prototype('variable')->end()
                        ->end()
                        ->arrayNode('formats')
                            ->useAttributeAsKey('name')
                            ->defaultValue(array('json' => true, 'xml' => true))
                            ->prototype('boolean')->end()
                        ->end()
                        ->arrayNode('templating_formats')
                            ->useAttributeAsKey('name')
                            ->defaultValue(array('html' => true))
                            ->prototype('boolean')->end()
                        ->end()
                        ->scalarNode('view_response_listener')->defaultValue('force')
                            ->validate()
                                ->ifNotInArray($this->forceOptionValues)
                                ->thenInvalid('The view_response_listener option does not support %s. Please choose one of '.json_encode($this->forceOptionValues))
                            ->end()
                        ->end()
                        ->scalarNode('failed_validation')->defaultValue(Codes::HTTP_BAD_REQUEST)->end()
                    ->end()
                ->end()
            ->end();
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
                        ->booleanNode('prefer_extension')->defaultTrue()->end()
                        ->scalarNode('fallback_format')->defaultValue('html')->end()
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
                            ->prototype('boolean')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
