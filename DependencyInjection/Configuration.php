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
            ->children()
                ->scalarNode('disable_csrf_role')->defaultNull()->end()
                ->arrayNode('access_denied_listener')
                    ->canBeEnabled()
                    ->beforeNormalization()
                        ->ifArray()->then(function ($v) { if (!empty($v) && empty($v['formats'])) {
     unset($v['enabled']);
     $v = array('enabled' => true, 'formats' => $v);
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
                        ->then(function ($v) { return array('enabled' => in_array($v, array('force', 'true')), 'force' => 'force' === $v); })
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
                            ->canBeEnabled()
                            ->beforeNormalization()
                                ->ifArray()->then(function ($v) { if (!empty($v) && empty($v['formats'])) {
     unset($v['enabled']);
     $v = array('enabled' => true, 'formats' => $v);
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
                            ->defaultValue(array('json' => true, 'xml' => true))
                            ->prototype('boolean')->end()
                        ->end()
                        ->arrayNode('templating_formats')
                            ->useAttributeAsKey('name')
                            ->defaultValue(array('html' => true))
                            ->prototype('boolean')->end()
                        ->end()
                        ->arrayNode('view_response_listener')
                            ->beforeNormalization()
                                ->ifString()
                                ->then(function ($v) { return array('enabled' => in_array($v, array('force', 'true')), 'force' => 'force' === $v); })
                            ->end()
                            ->canBeEnabled()
                            ->children()
                                ->booleanNode('enabled')->defaultFalse()->end()
                                ->booleanNode('force')->defaultFalse()->end()
                                ->scalarNode('service')->defaultNull()->end()
                            ->end()
                        ->end()
                        ->scalarNode('failed_validation')->defaultValue(Codes::HTTP_BAD_REQUEST)->end()
                        ->scalarNode('empty_content')->defaultValue(Codes::HTTP_NO_CONTENT)->end()
                        ->scalarNode('exception_wrapper_handler')->defaultNull()->end()
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
                    ->canBeDisabled()
                    ->children()
                        ->scalarNode('service')->defaultNull()->end()
                        ->scalarNode('default_format')->defaultNull()->end()
                        ->booleanNode('throw_exception_on_unsupported_content_type')
                            ->defaultFalse()
                        ->end()
                        ->arrayNode('decoders')
                            ->useAttributeAsKey('name')
                            ->defaultValue(array('json' => 'fos_rest.decoder.json', 'xml' => 'fos_rest.decoder.xml'))
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('array_normalizer')
                            ->addDefaultsIfNotSet()
                            ->beforeNormalization()
                                ->ifString()->then(function ($v) { return array('service' => $v); })
                            ->end()
                            ->children()
                                ->scalarNode('service')->defaultNull()->end()
                                ->booleanNode('forms')->defaultFalse()->end()
                            ->end()
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
                    ->beforeNormalization()
                        ->ifTrue(function ($v) {
                            // check if we got an assoc array in rules
                            return isset($v['rules'])
                                && is_array($v['rules'])
                                && array_keys($v['rules']) !== range(0, count($v['rules']) - 1);
                        })
                        ->then(function ($v) {
                            $v['rules'] = array($v['rules']);

                            return $v;
                        })
                    ->end()
                    ->canBeEnabled()
                    ->validate()
                        ->ifTrue(function ($v) { return empty($v['rules']) && !empty($v['media_type']['enabled']); })
                        ->thenInvalid('To enable the "media_type" setting, a "rules" setting must also needs to be defined for the "format_listener"')
                    ->end()
                    ->children()
                        ->scalarNode('service')->defaultNull()->end()
                        ->arrayNode('rules')
                            ->cannotBeOverwritten()
                            ->prototype('array')
                                ->fixXmlConfig('priority', 'priorities')
                                ->children()
                                    ->scalarNode('path')->defaultNull()->info('URL path info')->end()
                                    ->scalarNode('host')->defaultNull()->info('URL host name')->end()
                                    ->variableNode('methods')->defaultNull()->info('Method for URL')->end()
                                    ->booleanNode('stop')->defaultFalse()->end()
                                    ->booleanNode('prefer_extension')->defaultTrue()->end()
                                    ->scalarNode('fallback_format')->defaultValue('html')->end()
                                    ->scalarNode('exception_fallback_format')->defaultNull()->end()
                                    ->arrayNode('priorities')
                                        ->beforeNormalization()->ifString()->then(function ($v) { return preg_split('/\s*,\s*/', $v); })->end()
                                        ->prototype('scalar')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('media_type')
                            ->canBeEnabled()
                            ->beforeNormalization()
                                ->ifString()
                                ->then(function ($v) { return array('enabled' => true, 'version_regex' => $v); })
                            ->end()
                            ->children()
                                ->scalarNode('service')->defaultNull()->end()
                                ->scalarNode('version_regex')->defaultValue('/(v|version)=(?P<version>[0-9\.]+)/')->end()
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
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('exception_controller')->defaultNull()->end()
                        ->arrayNode('codes')
                            ->useAttributeAsKey('name')
                            ->validate()
                                ->ifTrue(function ($v) { return 0 !== count(array_filter($v, function ($i) { return !defined('FOS\RestBundle\Util\Codes::'.$i) && !is_int($i); })); })
                                ->thenInvalid('Invalid HTTP code in fos_rest.exception.codes, see FOS\RestBundle\Util\Codes for all valid codes.')
                            ->end()
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
