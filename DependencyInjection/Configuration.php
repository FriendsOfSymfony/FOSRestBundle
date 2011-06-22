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
            ->fixXmlConfig('force_redirect', 'force_redirects')
            ->fixXmlConfig('normalizer', 'normalizers')
            ->fixXmlConfig('class', 'classes')
            ->fixXmlConfig('service', 'services')
            ->children()
                ->scalarNode('default_form_key')->defaultNull()->end()
                ->arrayNode('formats')
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('force_redirects')
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('normalizers')
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
                    ->treatTrueLike(array('default_priorities' => array('html', '*/*'), 'fallback_format' => 'html'))
                    ->children()
                        ->arrayNode('default_priorities')
                            ->treatTrueLike(array('html', '*/*'))
                            ->prototype('scalar')->end()
                        ->end()
                        ->scalarNode('fallback_format')->defaultValue('html')->end()
                    ->end()
                ->end()
                ->arrayNode('routing_loader')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('default_format')->defaultNull()->end()
                    ->end()
                ->end()
                ->booleanNode('body_listener')->defaultFalse()->end()
                ->arrayNode('flash_message_listener')
                    ->treatTrueLike(array('name' => 'flashes', 'path' => '/', 'domain' => null, 'secure' => false, 'httpOnly' => true))
                    ->children()
                        ->scalarNode('name')->defaultValue('flashes')->end()
                        ->scalarNode('path')->defaultValue('/')->end()
                        ->scalarNode('domain')->defaultNull()->end()
                        ->scalarNode('secure')->defaultFalse('')->end()
                        ->scalarNode('httpOnly')->defaultTrue('')->end()
                    ->end()
                ->end()
                ->booleanNode('frameworkextra_bundle')->defaultFalse()->end()
                ->booleanNode('serializer_bundle')->defaultFalse()->end()
                ->scalarNode('failed_validation')->defaultValue(Codes::HTTP_BAD_REQUEST)->end()
                ->arrayNode('classes')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('view')->defaultValue('FOS\RestBundle\View\View')->end()
                        ->scalarNode('serializer')->defaultValue('Symfony\Component\Serializer\Serializer')->end()
                        ->scalarNode('json')->defaultValue('Symfony\Component\Serializer\Encoder\JsonEncoder')->end()
                        ->scalarNode('xml')->defaultValue('Symfony\Component\Serializer\Encoder\XmlEncoder')->end()
                        ->scalarNode('html')->defaultValue('FOS\RestBundle\Serializer\Encoder\HtmlEncoder')->end()
                        ->scalarNode('body_listener')->defaultValue('FOS\RestBundle\EventListener\RequestListener')->end()
                        ->scalarNode('format_listener')->defaultValue('FOS\RestBundle\EventListener\ControllerListener')->end()
                        ->scalarNode('flash_message_listener')->defaultValue('FOS\RestBundle\EventListener\FlashMessageListener')->end()
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
