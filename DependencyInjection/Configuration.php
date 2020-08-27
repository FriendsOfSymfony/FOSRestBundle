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
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

/**
 * This class contains the configuration information for the bundle.
 *
 * This information is solely responsible for how the different configuration
 * sections are normalized, and merged.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 *
 * @internal
 */
final class Configuration implements ConfigurationInterface
{
    private $debug;

    public function __construct(bool $debug)
    {
        $this->debug = $debug;
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('fos_rest');

        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('disable_csrf_role')->defaultNull()->end()
                ->scalarNode('unauthorized_challenge')->defaultNull()->end()
                ->arrayNode('param_fetcher_listener')
                    ->beforeNormalization()
                        ->ifString()
                        ->then(function ($v) {
                            return ['enabled' => in_array($v, ['force', 'true']), 'force' => 'force' === $v];
                        })
                    ->end()
                    ->canBeEnabled()
                    ->children()
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
                ->booleanNode('routing_loader')
                    ->defaultValue(false)
                    ->validate()
                        ->ifTrue()
                        ->thenInvalid('only "false" is supported')
                    ->end()
                ->end()
                ->arrayNode('body_converter')
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('validate')
                            ->defaultFalse()
                            ->beforeNormalization()
                                ->ifTrue()
                                ->then(function ($value) {
                                    if (!class_exists(OptionsResolver::class)) {
                                        throw new InvalidConfigurationException("'body_converter.validate: true' requires OptionsResolver component installation ( composer require symfony/options-resolver )");
                                    }

                                    return $value;
                                })
                            ->end()
                        ->end()
                        ->scalarNode('validation_errors_argument')->defaultValue('validationErrors')->end()
                    ->end()
                ->end()
                ->arrayNode('service')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('serializer')->defaultNull()->end()
                        ->scalarNode('view_handler')->defaultValue('fos_rest.view_handler.default')->end()
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
                ->arrayNode('zone')
                    ->cannotBeOverwritten()
                    ->prototype('array')
                    ->fixXmlConfig('ip')
                    ->children()
                        ->scalarNode('path')
                            ->defaultNull()
                            ->info('use the urldecoded format')
                            ->example('^/path to resource/')
                        ->end()
                        ->scalarNode('host')->defaultNull()->end()
                        ->arrayNode('methods')
                            ->beforeNormalization()->ifString()->then(function ($v) {
                                return preg_split('/\s*,\s*/', $v);
                            })->end()
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('ips')
                            ->beforeNormalization()->ifString()->then(function ($v) {
                                return [$v];
                            })->end()
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
        $this->addVersioningSection($rootNode);

        return $treeBuilder;
    }

    private function addViewSection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
                ->arrayNode('view')
                    ->fixXmlConfig('format', 'formats')
                    ->fixXmlConfig('mime_type', 'mime_types')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('mime_types')
                            ->canBeEnabled()
                            ->beforeNormalization()
                                ->ifArray()->then(function ($v) {
                                    if (!empty($v) && empty($v['formats'])) {
                                        unset($v['enabled']);
                                        $v = ['enabled' => true, 'formats' => $v];
                                    }

                                    return $v;
                                })
                            ->end()
                            ->fixXmlConfig('format', 'formats')
                            ->children()
                                ->scalarNode('service')->defaultNull()->end()
                                ->arrayNode('formats')
                                    ->useAttributeAsKey('name')
                                    ->prototype('array')
                                        ->beforeNormalization()
                                            ->ifString()
                                            ->then(function ($v) { return [$v]; })
                                        ->end()
                                        ->prototype('scalar')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('formats')
                            ->useAttributeAsKey('name')
                            ->defaultValue(['json' => true, 'xml' => true])
                            ->prototype('boolean')->end()
                        ->end()
                        ->arrayNode('view_response_listener')
                            ->beforeNormalization()
                                ->ifString()
                                ->then(function ($v) {
                                    return ['enabled' => in_array($v, ['force', 'true']), 'force' => 'force' === $v];
                                })
                            ->end()
                            ->canBeEnabled()
                            ->children()
                                ->booleanNode('force')->defaultFalse()->end()
                                ->scalarNode('service')->defaultNull()->end()
                            ->end()
                        ->end()
                        ->scalarNode('failed_validation')->defaultValue(Response::HTTP_BAD_REQUEST)->end()
                        ->scalarNode('empty_content')->defaultValue(Response::HTTP_NO_CONTENT)->end()
                        ->booleanNode('serialize_null')->defaultFalse()->end()
                        ->arrayNode('jsonp_handler')
                            ->canBeUnset()
                            ->children()
                                ->scalarNode('callback_param')->defaultValue('callback')->end()
                                ->scalarNode('mime_type')->defaultValue('application/javascript+jsonp')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addBodyListenerSection(ArrayNodeDefinition $rootNode): void
    {
        $decodersDefaultValue = ['json' => 'fos_rest.decoder.json'];
        if (class_exists(XmlEncoder::class)) {
            $decodersDefaultValue['xml'] = 'fos_rest.decoder.xml';
        }
        $rootNode
            ->children()
                ->arrayNode('body_listener')
                    ->fixXmlConfig('decoder', 'decoders')
                    ->addDefaultsIfNotSet()
                    ->canBeUnset()
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('service')->defaultNull()->end()
                        ->scalarNode('default_format')->defaultNull()->end()
                        ->booleanNode('throw_exception_on_unsupported_content_type')
                            ->defaultFalse()
                        ->end()
                        ->arrayNode('decoders')
                            ->useAttributeAsKey('name')
                            ->defaultValue($decodersDefaultValue)
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('array_normalizer')
                            ->addDefaultsIfNotSet()
                            ->beforeNormalization()
                                ->ifString()->then(function ($v) {
                                    return ['service' => $v];
                                })
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

    private function addFormatListenerSection(ArrayNodeDefinition $rootNode): void
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
                            $v['rules'] = [$v['rules']];

                            return $v;
                        })
                    ->end()
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('service')->defaultNull()->end()
                        ->arrayNode('rules')
                            ->performNoDeepMerging()
                            ->prototype('array')
                                ->fixXmlConfig('priority', 'priorities')
                                ->fixXmlConfig('attribute', 'attributes')
                                ->children()
                                    ->scalarNode('path')->defaultNull()->info('URL path info')->end()
                                    ->scalarNode('host')->defaultNull()->info('URL host name')->end()
                                    ->variableNode('methods')->defaultNull()->info('Method for URL')->end()
                                    ->arrayNode('attributes')
                                        ->useAttributeAsKey('name')
                                        ->prototype('variable')->end()
                                    ->end()
                                    ->booleanNode('stop')->defaultFalse()->end()
                                    ->booleanNode('prefer_extension')->defaultTrue()->end()
                                    ->scalarNode('fallback_format')->defaultValue('html')->end()
                                    ->arrayNode('priorities')
                                        ->beforeNormalization()->ifString()->then(function ($v) {
                                            return preg_split('/\s*,\s*/', $v);
                                        })->end()
                                        ->prototype('scalar')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addVersioningSection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
        ->children()
            ->arrayNode('versioning')
                ->canBeEnabled()
                ->children()
                    ->scalarNode('default_version')->defaultNull()->end()
                    ->arrayNode('resolvers')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->arrayNode('query')
                                ->canBeDisabled()
                                ->children()
                                    ->scalarNode('parameter_name')->defaultValue('version')->end()
                                ->end()
                            ->end()
                            ->arrayNode('custom_header')
                                ->canBeDisabled()
                                ->children()
                                    ->scalarNode('header_name')->defaultValue('X-Accept-Version')->end()
                                ->end()
                            ->end()
                            ->arrayNode('media_type')
                                ->canBeDisabled()
                                ->children()
                                    ->scalarNode('regex')->defaultValue('/(v|version)=(?P<version>[0-9\.]+)/')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('guessing_order')
                        ->defaultValue(['query', 'custom_header', 'media_type'])
                        ->validate()
                            ->ifTrue(function ($v) {
                                foreach ($v as $resolver) {
                                    if (!in_array($resolver, ['query', 'custom_header', 'media_type'])) {
                                        return true;
                                    }
                                }
                            })
                            ->thenInvalid('Versioning guessing order can only contain "query", "custom_header", "media_type".')
                        ->end()
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end()
        ->end();
    }

    private function addExceptionSection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
                ->arrayNode('exception')
                    ->fixXmlConfig('code', 'codes')
                    ->fixXmlConfig('message', 'messages')
                    ->addDefaultsIfNotSet()
                    ->canBeEnabled()
                    ->validate()
                      ->always()
                      ->then(function ($v) {
                          if (!$v['enabled']) {
                              return $v;
                          }

                          if ($v['exception_listener']) {
                              @trigger_error('Enabling the "fos_rest.exception.exception_listener" option is deprecated since FOSRestBundle 2.8.', E_USER_DEPRECATED);
                          }
                          if ($v['serialize_exceptions']) {
                              @trigger_error('Enabling the "fos_rest.exception.serialize_exceptions" option is deprecated since FOSRestBundle 2.8.', E_USER_DEPRECATED);
                          }

                          return $v;
                      })
                    ->end()
                    ->children()
                        ->booleanNode('map_exception_codes')
                            ->defaultFalse()
                            ->info('Enables an event listener that maps exception codes to response status codes based on the map configured with the "fos_rest.exception.codes" option.')
                        ->end()
                        ->booleanNode('exception_listener')
                            ->defaultValue(false)
                            ->validate()
                                ->ifTrue()
                                ->thenInvalid('only "false" is supported')
                            ->end()
                        ->end()
                        ->booleanNode('serialize_exceptions')
                            ->defaultValue(false)
                            ->validate()
                                ->ifTrue()
                                ->thenInvalid('only "false" is supported')
                            ->end()
                        ->end()
                        ->enumNode('flatten_exception_format')
                            ->defaultValue('legacy')
                            ->values(['legacy', 'rfc7807'])
                        ->end()
                        ->booleanNode('serializer_error_renderer')->defaultValue(false)->end()
                        ->arrayNode('codes')
                            ->useAttributeAsKey('name')
                            ->beforeNormalization()
                                ->ifArray()
                                ->then(function (array $items) {
                                    foreach ($items as &$item) {
                                        if (is_int($item)) {
                                            continue;
                                        }

                                        if (!defined(sprintf('%s::%s', Response::class, $item))) {
                                            throw new InvalidConfigurationException(sprintf('Invalid HTTP code in fos_rest.exception.codes, see %s for all valid codes.', Response::class));
                                        }

                                        $item = constant(sprintf('%s::%s', Response::class, $item));
                                    }

                                    return $items;
                                })
                            ->end()
                            ->prototype('integer')->end()

                            ->validate()
                            ->ifArray()
                                ->then(function (array $items) {
                                    foreach ($items as $class => $code) {
                                        $this->testExceptionExists($class);
                                    }

                                    return $items;
                                })
                            ->end()
                        ->end()
                        ->arrayNode('messages')
                            ->useAttributeAsKey('name')
                            ->prototype('boolean')->end()
                            ->validate()
                                ->ifArray()
                                ->then(function (array $items) {
                                    foreach ($items as $class => $nomatter) {
                                        $this->testExceptionExists($class);
                                    }

                                    return $items;
                                })
                            ->end()
                        ->end()
                        ->booleanNode('debug')
                            ->defaultValue($this->debug)
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function testExceptionExists(string $throwable): void
    {
        if (!is_a($throwable, \Throwable::class, true)) {
            throw new InvalidConfigurationException(sprintf('FOSRestBundle exception mapper: Could not load class "%s" or the class does not extend from "%s". Most probably this is a configuration problem.', $throwable, \Throwable::class));
        }
    }
}
