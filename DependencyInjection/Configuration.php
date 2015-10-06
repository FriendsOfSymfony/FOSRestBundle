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

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * This class contains the configuration information for the bundle.
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
            ->fixXmlConfig('zone_rule', 'zone_rules')
            ->fixXmlConfig('zone', 'zones')
            ->children()
                ->arrayNode('zone_rules')
                    ->cannotBeOverwritten()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('path')->defaultNull()->info('URL path info')->end()
                            ->scalarNode('host')->defaultNull()->info('URL host name')->end()
                            ->variableNode('methods')->defaultNull()->info('Method for URL')->end()
                            ->variableNode('zone')->defaultValue('default')->info('Zone to apply for this rule')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('zones')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->append($this->addViewSection())
                            ->append($this->addExceptionSection())
                            ->append($this->addBodyListenerSection())
                            ->append($this->addFormatListenerSection())
                            ->scalarNode('disable_csrf_role')->defaultNull()->end()
                            ->arrayNode('access_denied_listener')
                                ->canBeEnabled()
                                ->beforeNormalization()
                                    ->ifArray()->then(function ($v) { if (!empty($v) && empty($v['formats'])) {
     unset($v['enabled']);
     $v = ['enabled' => true, 'formats' => $v];
 }

return $v; })
                                ->end()
                                ->fixXmlConfig('format', 'formats')
                                ->children()
                                    ->scalarNode('service')->defaultNull()->end()
                                    ->arrayNode('formats')
                                        ->useAttributeAsKey('name')
                                        ->prototype('boolean')->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->scalarNode('unauthorized_challenge')->defaultNull()->end()
                            ->arrayNode('param_fetcher_listener')
                                ->beforeNormalization()
                                    ->ifString()
                                    ->then(function ($v) { return ['enabled' => in_array($v, ['force', 'true']), 'force' => 'force' === $v]; })
                                ->end()
                                ->canBeEnabled()
                                ->children()
                                    ->booleanNode('enabled')->defaultFalse()->end()
                                    ->booleanNode('force')->defaultFalse()->end()
                                    ->scalarNode('service')->defaultNull()->end()
                                ->end()
                            ->end()
                            ->scalarNode('cache_dir')->cannotBeEmpty()->defaultValue('%kernel.cache_dir%/fos_rest')->end()
                            ->arrayNode('allowed_methods_listener')
                                ->canBeEnabled()
                                ->children()
                                    ->scalarNode('service')->defaultNull()->end()
                                ->end()
                            ->end()
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
                                    ->scalarNode('serializer')->defaultNull()->end()
                                    ->scalarNode('view_handler')->defaultValue('fos_rest.view_handler.default')->end()
                                    ->scalarNode('exception_handler')->defaultValue('fos_rest.view.exception_wrapper_handler')->end()
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
                    ->end()
                ->end()
        ->end();

        return $treeBuilder;
    }

    private function addViewSection()
    {
        $builder = new TreeBuilder();
        $rootNode = $builder->root('view');

        $rootNode
            ->fixXmlConfig('format', 'formats')
            ->fixXmlConfig('mime_type', 'mime_types')
            ->fixXmlConfig('templating_format', 'templating_formats')
            ->fixXmlConfig('force_redirect', 'force_redirects')
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('default_engine')->defaultValue('twig')->end()
                ->arrayNode('force_redirects')
                    ->useAttributeAsKey('name')
                    ->defaultValue(['html' => true])
                    ->prototype('boolean')->end()
                ->end()
                ->arrayNode('mime_types')
                    ->canBeEnabled()
                    ->beforeNormalization()
                        ->ifArray()->then(function ($v) { if (!empty($v) && empty($v['formats'])) {
     unset($v['enabled']);
     $v = ['enabled' => true, 'formats' => $v];
 }

return $v; })
                    ->end()
                    ->fixXmlConfig('format', 'formats')
                    ->children()
                        ->scalarNode('service')->defaultNull()->end()
                        ->arrayNode('formats')
                            ->useAttributeAsKey('name')
                            ->prototype('variable')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('formats')
                    ->useAttributeAsKey('name')
                    ->defaultValue(['json' => true, 'xml' => true])
                    ->prototype('boolean')->end()
                ->end()
                ->arrayNode('templating_formats')
                    ->useAttributeAsKey('name')
                    ->defaultValue(['html' => true])
                    ->prototype('boolean')->end()
                ->end()
                ->arrayNode('view_response_listener')
                    ->beforeNormalization()
                        ->ifString()
                        ->then(function ($v) { return ['enabled' => in_array($v, ['force', 'true']), 'force' => 'force' === $v]; })
                    ->end()
                    ->canBeEnabled()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                        ->booleanNode('force')->defaultFalse()->end()
                        ->scalarNode('service')->defaultNull()->end()
                    ->end()
                ->end()
                ->scalarNode('failed_validation')->defaultValue(Response::HTTP_BAD_REQUEST)->end()
                ->scalarNode('empty_content')->defaultValue(Response::HTTP_NO_CONTENT)->end()
                ->scalarNode('exception_wrapper_handler')->defaultNull()->end()
                ->booleanNode('serialize_null')->defaultFalse()->end()
                ->arrayNode('jsonp_handler')
                    ->canBeUnset()
                    ->children()
                        ->scalarNode('callback_param')->defaultValue('callback')->end()
                        ->scalarNode('mime_type')->defaultValue('application/javascript+jsonp')->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $rootNode;
    }

    private function addBodyListenerSection()
    {
        $builder = new TreeBuilder();
        $rootNode = $builder->root('body_listener');

        $rootNode
            ->fixXmlConfig('decoder', 'decoders')
            ->addDefaultsIfNotSet()
            ->canBeUnset()
            ->canBeDisabled()
            ->children()
                ->scalarNode('service')->defaultNull()->end()
                ->scalarNode('default_format')->defaultNull()->end()
                ->booleanNode('throw_exception_on_unsupported_content_type')
                    ->defaultFalse()
                ->end()
                ->arrayNode('decoders')
                    ->useAttributeAsKey('name')
                    ->defaultValue(['json' => 'fos_rest.decoder.json', 'xml' => 'fos_rest.decoder.xml'])
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('array_normalizer')
                    ->addDefaultsIfNotSet()
                    ->beforeNormalization()
                        ->ifString()->then(function ($v) { return ['service' => $v]; })
                    ->end()
                    ->children()
                        ->scalarNode('service')->defaultNull()->end()
                        ->booleanNode('forms')->defaultFalse()->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $rootNode;
    }

    private function addFormatListenerSection()
    {
        $builder = new TreeBuilder();
        $rootNode = $builder->root('format_listener');

        $rootNode
            ->addDefaultsIfNotSet()
            ->canBeEnabled()
            ->fixXmlConfig('priority', 'priorities')
            ->children()
                ->scalarNode('service')->defaultNull()->end()
                ->booleanNode('prefer_extension')->defaultTrue()->end()
                ->scalarNode('fallback_format')->defaultValue('html')->end()
                ->scalarNode('exception_fallback_format')->defaultNull()->end()
                ->arrayNode('priorities')
                    ->beforeNormalization()->ifString()->then(function ($v) { return preg_split('/\s*,\s*/', $v); })->end()
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('media_type')
                    ->canBeEnabled()
                    ->beforeNormalization()
                        ->ifString()
                        ->then(function ($v) { return ['enabled' => true, 'version_regex' => $v]; })
                    ->end()
                    ->children()
                        ->scalarNode('service')->defaultNull()->end()
                        ->scalarNode('version_regex')->defaultValue('/(v|version)=(?P<version>[0-9\.]+)/')->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $rootNode;
    }

    private function addExceptionSection()
    {
        $builder = new TreeBuilder();
        $rootNode = $builder->root('exception');

        $rootNode
            ->fixXmlConfig('code', 'codes')
            ->fixXmlConfig('message', 'messages')
            ->addDefaultsIfNotSet()
            ->canBeEnabled()
            ->children()
                ->scalarNode('exception_controller')->defaultNull()->end()
                ->arrayNode('codes')
                    ->useAttributeAsKey('name')
                    ->validate()
                        ->ifTrue(function ($v) { return 0 !== count(array_filter($v, function ($i) { return !defined('Symfony\Component\HttpFoundation\Response::'.$i) && !is_int($i); })); })
                        ->thenInvalid('Invalid HTTP code in fos_rest.exception.codes, see Symfony\Component\HttpFoundation\Response for all valid codes.')
                    ->end()
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('messages')
                    ->useAttributeAsKey('name')
                    ->prototype('boolean')->end()
                ->end()
            ->end()
        ->end();

        return $rootNode;
    }
}
