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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use FOS\RestBundle\Util\Codes;

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
                ->scalarNode('disable_csrf_role')->defaultNull()->end()
                ->arrayNode('access_denied_listener')
                    ->useAttributeAsKey('name')
                    ->prototype('boolean')->end()
                ->end()
                ->scalarNode('param_fetcher_listener')->defaultFalse()
                    ->validate()
                        ->ifNotInArray($this->forceOptionValues)
                        ->thenInvalid('The param_fetcher_listener option does not support %s. Please choose one of '.json_encode($this->forceOptionValues))
                    ->end()
                ->end()
                ->scalarNode('cache_dir')->cannotBeEmpty()->defaultValue('%kernel.cache_dir%/fos_rest')->end()
                ->scalarNode('allowed_methods_listener')->defaultFalse()->end()
                ->arrayNode('routing_loader')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('default_format')->defaultNull()->end()
                        ->scalarNode('include_format')->defaultTrue()->end()
                    ->end()
                ->end()
                ->arrayNode('body_converter')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('enabled')->defaultFalse()->end()
                        ->scalarNode('validate')->defaultFalse()->end()
                        ->scalarNode('validation_errors_argument')->defaultValue('validationErrors')->end()
                    ->end()
                ->end()
                ->arrayNode('service')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('router')->defaultValue('router')->end()
                        ->scalarNode('templating')->defaultValue('templating')->end()
                        ->scalarNode('serializer')->defaultValue('jms_serializer.serializer')->end()
                        ->scalarNode('view_handler')->defaultValue('fos_rest.view_handler.default')->end()
                        ->scalarNode('inflector')->defaultValue('fos_rest.inflector.doctrine')->end()
                        ->scalarNode('validator')->defaultValue('validator')->end()
                    ->end()
                ->end()
                ->arrayNode('serializer')
                    ->validate()
                        ->ifTrue(function ($v) { return !empty($v['version']) && !empty($v['groups']); })
                        ->thenInvalid('Only either a version or a groups exclusion strategy can be set')
                    ->end()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('version')->defaultNull()->end()
                        ->arrayNode('groups')
                            ->prototype('scalar')->end()
                        ->end()
                        ->booleanNode('serialize_null')->defaultFalse()->end()
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
                        ->scalarNode('view_response_listener')->defaultFalse()
                            ->validate()
                                ->ifNotInArray($this->forceOptionValues)
                                ->thenInvalid('The view_response_listener option does not support %s. Please choose one of '.json_encode($this->forceOptionValues))
                            ->end()
                        ->end()
                        ->scalarNode('failed_validation')->defaultValue(Codes::HTTP_BAD_REQUEST)->end()
                        ->scalarNode('empty_content')->defaultValue(Codes::HTTP_NO_CONTENT)->end()
                        ->scalarNode('exception_wrapper_handler')->defaultValue('FOS\RestBundle\View\ExceptionWrapperHandler')->end()
                        ->booleanNode('serialize_null')->defaultFalse()->end()
                        ->arrayNode('jsonp_handler')
                            ->canBeUnset()
                            ->children()
                                ->scalarNode('callback_param')->defaultValue('callback')->end()
                                ->scalarNode('callback_filter')->defaultValue('/(^[a-z0-9_]+$)|(^YUI\.Env\.JSONP\._[0-9]+$)/i')->end()
                                ->scalarNode('mime_type')->defaultValue('application/javascript+jsonp')->end()
                            ->end()
                        ->end()
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
                        ->booleanNode('throw_exception_on_unsupported_content_type')
                            ->defaultFalse()
                        ->end()
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
                    ->fixXmlConfig('rule', 'rules')
                    ->addDefaultsIfNotSet()
                    ->canBeUnset()
                    ->children()
                        ->arrayNode('rules')
                            ->cannotBeOverwritten()
                            ->prototype('array')
                                ->fixXmlConfig('priority', 'priorities')
                                ->children()
                                    ->scalarNode('path')->defaultNull()->info('URL path info')->end()
                                    ->scalarNode('host')->defaultNull()->info('URL host name')->end()
                                    ->variableNode('methods')->defaultNull()->info('Method for URL')->end()
                                    ->booleanNode('prefer_extension')->defaultTrue()->end()
                                    ->scalarNode('fallback_format')->defaultValue('html')->end()
                                    ->arrayNode('priorities')
                                        ->beforeNormalization()->ifString()->then(function ($v) { return preg_split('/\s*,\s*/', $v); })->end()
                                        ->prototype('scalar')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('media_type')
                            ->children()
                                ->scalarNode('version_regex')
                                    ->defaultValue('/(v|version)=(?P<version>[0-9\.]+)/')
                                ->end()
                            ->end()
                        ->end()
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
