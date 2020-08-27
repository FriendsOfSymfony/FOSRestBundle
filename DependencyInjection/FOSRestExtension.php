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

use FOS\RestBundle\ErrorRenderer\SerializerErrorRenderer;
use FOS\RestBundle\EventListener\ResponseStatusCodeListener;
use FOS\RestBundle\Inflector\DoctrineInflector;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\HttpKernel\EventListener\ErrorListener;
use Symfony\Component\HttpKernel\EventListener\ExceptionListener as LegacyHttpKernelExceptionListener;
use Symfony\Component\Validator\Constraint;

/**
 * @internal since 2.8
 */
class FOSRestExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration($container->getParameter('kernel.debug'));
    }

    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration($container->getParameter('kernel.debug'));
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('view.xml');
        $loader->load('request.xml');
        $loader->load('serializer.xml');

        $container->register('fos_rest.inflector.doctrine', DoctrineInflector::class)
            ->setDeprecated(true, 'The %service_id% service is deprecated since FOSRestBundle 2.8.')
            ->setPublic(false);

        if ($config['routing_loader']['enabled']) {
            $loader->load('routing.xml');

            $restRouteLoader = $container->getDefinition('fos_rest.routing.loader.controller');
            $restRouteLoader->addArgument(new Reference('controller_name_converter', ContainerInterface::NULL_ON_INVALID_REFERENCE));
            $restRouteLoader->addArgument(new Reference('fos_rest.routing.loader.reader.controller'));
            $restRouteLoader->addArgument($config['routing_loader']['default_format']);

            $container->getDefinition('fos_rest.routing.loader.yaml_collection')->replaceArgument(4, $config['routing_loader']['default_format']);
            $container->getDefinition('fos_rest.routing.loader.xml_collection')->replaceArgument(4, $config['routing_loader']['default_format']);

            $container->getDefinition('fos_rest.routing.loader.yaml_collection')->replaceArgument(2, $config['routing_loader']['include_format']);
            $container->getDefinition('fos_rest.routing.loader.xml_collection')->replaceArgument(2, $config['routing_loader']['include_format']);
            $container->getDefinition('fos_rest.routing.loader.reader.action')->replaceArgument(3, $config['routing_loader']['include_format']);
            $container->getDefinition('fos_rest.routing.loader.reader.action')->replaceArgument(5, $config['routing_loader']['prefix_methods']);
        }

        foreach ($config['service'] as $key => $service) {
            if ('validator' === $service && empty($config['body_converter']['validate'])) {
                continue;
            }

            if (null !== $service) {
                if ('view_handler' === $key) {
                    $container->setAlias('fos_rest.'.$key, new Alias($service, true));
                } elseif (in_array($key, ['inflector', 'router', 'templating'], true)) {
                    $alias = new Alias($service);

                    if (method_exists($alias, 'setDeprecated')) {
                        $alias->setDeprecated(true, 'The "%alias_id%" service alias is deprecated since FOSRestBundle 2.8.');
                    }

                    $container->setAlias('fos_rest.'.$key, $alias);
                } else {
                    $container->setAlias('fos_rest.'.$key, $service);
                }
            }
        }

        $this->loadForm($config, $loader, $container);
        $this->loadException($config, $loader, $container);
        $this->loadBodyConverter($config, $loader, $container);
        $this->loadView($config, $loader, $container);

        $this->loadBodyListener($config, $loader, $container);
        $this->loadFormatListener($config, $loader, $container);
        $this->loadVersioning($config, $loader, $container);
        $this->loadParamFetcherListener($config, $loader, $container);
        $this->loadAllowedMethodsListener($config, $loader, $container);
        $this->loadAccessDeniedListener($config, $loader, $container);
        $this->loadZoneMatcherListener($config, $loader, $container);

        // Needs RequestBodyParamConverter and View Handler loaded.
        $this->loadSerializer($config, $container);
    }

    private function loadForm(array $config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        if (!empty($config['disable_csrf_role'])) {
            $loader->load('forms.xml');

            $definition = $container->getDefinition('fos_rest.form.extension.csrf_disable');
            $definition->replaceArgument(1, $config['disable_csrf_role']);

            // BC for Symfony < 2.8: the extended_type attribute is used on higher versions
            if (!method_exists(AbstractType::class, 'getBlockPrefix')) {
                $definition->addTag('form.type_extension', ['alias' => 'form']);
            } else {
                $definition->addTag('form.type_extension', ['extended_type' => FormType::class]);
            }
        }
    }

    private function loadAccessDeniedListener(array $config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        if ($config['access_denied_listener']['enabled'] && !empty($config['access_denied_listener']['formats'])) {
            $loader->load('access_denied_listener.xml');

            $service = $container->getDefinition('fos_rest.access_denied_listener');

            if (!empty($config['access_denied_listener']['service'])) {
                $service->clearTag('kernel.event_subscriber');
            }

            $service->replaceArgument(0, $config['access_denied_listener']['formats']);
            $service->replaceArgument(1, $config['unauthorized_challenge']);
        }
    }

    private function loadAllowedMethodsListener(array $config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        if ($config['allowed_methods_listener']['enabled']) {
            if (!empty($config['allowed_methods_listener']['service'])) {
                $service = $container->getDefinition('fos_rest.allowed_methods_listener');
                $service->clearTag('kernel.event_listener');
            }

            $loader->load('allowed_methods_listener.xml');

            $container->getDefinition('fos_rest.allowed_methods_loader')->replaceArgument(1, $config['cache_dir']);
        }
    }

    private function loadBodyListener(array $config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        if ($config['body_listener']['enabled']) {
            $loader->load('body_listener.xml');

            $service = $container->getDefinition('fos_rest.body_listener');

            if (!empty($config['body_listener']['service'])) {
                $service->clearTag('kernel.event_listener');
            }

            $service->replaceArgument(1, $config['body_listener']['throw_exception_on_unsupported_content_type']);
            $service->addMethodCall('setDefaultFormat', [$config['body_listener']['default_format']]);

            $container->getDefinition('fos_rest.decoder_provider')->replaceArgument(1, $config['body_listener']['decoders']);

            if (class_exists(ServiceLocatorTagPass::class)) {
                $decoderServicesMap = [];

                foreach ($config['body_listener']['decoders'] as $id) {
                    $decoderServicesMap[$id] = new Reference($id);
                }

                $decodersServiceLocator = ServiceLocatorTagPass::register($container, $decoderServicesMap);
                $container->getDefinition('fos_rest.decoder_provider')->replaceArgument(0, $decodersServiceLocator);
            }

            $arrayNormalizer = $config['body_listener']['array_normalizer'];

            if (null !== $arrayNormalizer['service']) {
                $bodyListener = $container->getDefinition('fos_rest.body_listener');
                $bodyListener->addArgument(new Reference($arrayNormalizer['service']));
                $bodyListener->addArgument($arrayNormalizer['forms']);
            }
        }
    }

    private function loadFormatListener(array $config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        if ($config['format_listener']['enabled'] && !empty($config['format_listener']['rules'])) {
            $loader->load('format_listener.xml');

            if (!empty($config['format_listener']['service'])) {
                $service = $container->getDefinition('fos_rest.format_listener');
                $service->clearTag('kernel.event_listener');
            }

            $container->setParameter(
                'fos_rest.format_listener.rules',
                $config['format_listener']['rules']
            );
        }
    }

    private function loadVersioning(array $config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        if (!empty($config['versioning']['enabled'])) {
            $loader->load('versioning.xml');

            $versionListener = $container->getDefinition('fos_rest.versioning.listener');
            $versionListener->replaceArgument(1, $config['versioning']['default_version']);

            $resolvers = [];
            if ($config['versioning']['resolvers']['query']['enabled']) {
                $resolvers['query'] = $container->getDefinition('fos_rest.versioning.query_parameter_resolver');
                $resolvers['query']->replaceArgument(0, $config['versioning']['resolvers']['query']['parameter_name']);
            }
            if ($config['versioning']['resolvers']['custom_header']['enabled']) {
                $resolvers['custom_header'] = $container->getDefinition('fos_rest.versioning.header_resolver');
                $resolvers['custom_header']->replaceArgument(0, $config['versioning']['resolvers']['custom_header']['header_name']);
            }
            if ($config['versioning']['resolvers']['media_type']['enabled']) {
                $resolvers['media_type'] = $container->getDefinition('fos_rest.versioning.media_type_resolver');
                $resolvers['media_type']->replaceArgument(0, $config['versioning']['resolvers']['media_type']['regex']);
            }

            $chainResolver = $container->getDefinition('fos_rest.versioning.chain_resolver');
            foreach ($config['versioning']['guessing_order'] as $resolver) {
                if (isset($resolvers[$resolver])) {
                    $chainResolver->addMethodCall('addResolver', [$resolvers[$resolver]]);
                }
            }
        }
    }

    private function loadParamFetcherListener(array $config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        if ($config['param_fetcher_listener']['enabled']) {
            if (!class_exists(Constraint::class)) {
                @trigger_error('Enabling the fos_rest.param_fetcher_listener option when the Symfony Validator component is not installed is deprecated since FOSRestBundle 2.6 and will throw an exception in 3.0. Disable the feature or install the symfony/validator package.', E_USER_DEPRECATED);
            }

            $loader->load('param_fetcher_listener.xml');

            if (!empty($config['param_fetcher_listener']['service'])) {
                $service = $container->getDefinition('fos_rest.param_fetcher_listener');
                $service->clearTag('kernel.event_listener');
            }

            if ($config['param_fetcher_listener']['force']) {
                $container->getDefinition('fos_rest.param_fetcher_listener')->replaceArgument(1, true);
            }
        }
    }

    private function loadBodyConverter(array $config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        if (!$this->isConfigEnabled($container, $config['body_converter'])) {
            return;
        }

        $loader->load('request_body_param_converter.xml');

        if (!empty($config['body_converter']['validation_errors_argument'])) {
            $container->getDefinition('fos_rest.converter.request_body')->replaceArgument(4, $config['body_converter']['validation_errors_argument']);
        }
    }

    private function loadView(array $config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        if (!empty($config['view']['jsonp_handler'])) {
            $childDefinitionClass = class_exists(ChildDefinition::class) ? ChildDefinition::class : DefinitionDecorator::class;
            $handler = new $childDefinitionClass($config['service']['view_handler']);
            $handler->setPublic(true);

            $jsonpHandler = new Reference('fos_rest.view_handler.jsonp');
            $handler->addMethodCall('registerHandler', ['jsonp', [$jsonpHandler, 'createResponse']]);
            $container->setDefinition('fos_rest.view_handler', $handler);

            $container->getDefinition('fos_rest.view_handler.jsonp')->replaceArgument(0, $config['view']['jsonp_handler']['callback_param']);

            if (empty($config['view']['mime_types']['jsonp'])) {
                $config['view']['mime_types']['jsonp'] = $config['view']['jsonp_handler']['mime_type'];
            }
        }

        if ($config['view']['mime_types']['enabled']) {
            $loader->load('mime_type_listener.xml');

            if (!empty($config['mime_type_listener']['service'])) {
                $service = $container->getDefinition('fos_rest.mime_type_listener');
                $service->clearTag('kernel.event_listener');
            }

            $container->getDefinition('fos_rest.mime_type_listener')->replaceArgument(0, $config['view']['mime_types']['formats']);
        }

        if ($config['view']['view_response_listener']['enabled']) {
            $loader->load('view_response_listener.xml');
            $service = $container->getDefinition('fos_rest.view_response_listener');

            if (!empty($config['view_response_listener']['service'])) {
                $service->clearTag('kernel.event_listener');
            }

            $service->replaceArgument(1, $config['view']['view_response_listener']['force']);
        }

        $formats = [];
        foreach ($config['view']['formats'] as $format => $enabled) {
            if ($enabled) {
                $formats[$format] = false;
            }
        }
        foreach ($config['view']['templating_formats'] as $format => $enabled) {
            if ($enabled) {
                $formats[$format] = true;
            }
        }

        if ($config['routing_loader']['enabled']) {
            $container->getDefinition('fos_rest.routing.loader.yaml_collection')->replaceArgument(3, $formats);
            $container->getDefinition('fos_rest.routing.loader.xml_collection')->replaceArgument(3, $formats);
            $container->getDefinition('fos_rest.routing.loader.reader.action')->replaceArgument(4, $formats);
        }

        foreach ($config['view']['force_redirects'] as $format => $code) {
            if (true === $code) {
                $config['view']['force_redirects'][$format] = Response::HTTP_FOUND;
            }
        }

        if (!is_numeric($config['view']['failed_validation'])) {
            $config['view']['failed_validation'] = constant(sprintf('%s::%s', Response::class, $config['view']['failed_validation']));
        }

        if (!is_numeric($config['view']['empty_content'])) {
            $config['view']['empty_content'] = constant(sprintf('%s::%s', Response::class, $config['view']['empty_content']));
        }

        $defaultViewHandler = $container->getDefinition('fos_rest.view_handler.default');

        $defaultViewHandler->setArguments([
            new Reference($config['service']['router']),
            new Reference('fos_rest.serializer'),
            new Reference('fos_rest.templating', ContainerInterface::NULL_ON_INVALID_REFERENCE),
            new Reference('request_stack'),
            $formats,
            $config['view']['failed_validation'],
            $config['view']['empty_content'],
            $config['view']['serialize_null'],
            $config['view']['force_redirects'],
            $config['view']['default_engine'],
            [],
            false,
        ]);
    }

    private function loadException(array $config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        if ($config['exception']['enabled']) {
            $loader->load('exception.xml');

            if ($config['exception']['map_exception_codes']) {
                $container->register('fos_rest.exception.response_status_code_listener', ResponseStatusCodeListener::class)
                    ->setArguments([
                        new Reference('fos_rest.exception.codes_map'),
                    ])
                    ->addTag('kernel.event_subscriber');
            }

            if ($config['exception']['exception_listener']) {
                if (!empty($config['exception']['service'])) {
                    $service = $container->getDefinition('fos_rest.exception_listener');
                    $service->clearTag('kernel.event_subscriber');
                }

                $controller = $config['exception']['exception_controller'] ?? null;

                if (class_exists(ErrorListener::class)) {
                    $container->register('fos_rest.error_listener', ErrorListener::class)
                        ->setArguments([
                            $controller,
                            new Reference('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE),
                            '%kernel.debug%',
                        ])
                        ->addTag('monolog.logger', ['channel' => 'request']);
                } else {
                    $container->register('fos_rest.error_listener', LegacyHttpKernelExceptionListener::class)
                        ->setArguments([
                            $controller,
                            new Reference('logger', ContainerInterface::NULL_ON_INVALID_REFERENCE),
                        ])
                        ->addTag('monolog.logger', ['channel' => 'request']);
                }

                $container->getDefinition('fos_rest.exception.controller')
                    ->replaceArgument(2, $config['exception']['debug']);
            } else {
                $container->removeDefinition('fos_rest.exception_listener');
            }

            $container->getDefinition('fos_rest.exception.codes_map')
                ->replaceArgument(0, $config['exception']['codes']);
            $container->getDefinition('fos_rest.exception.messages_map')
                ->replaceArgument(0, $config['exception']['messages']);

            $container->getDefinition('fos_rest.serializer.flatten_exception_handler')
                ->replaceArgument(2, $config['exception']['debug']);
            $container->getDefinition('fos_rest.serializer.flatten_exception_handler')
                ->replaceArgument(3, 'rfc7807' === $config['exception']['flatten_exception_format']);
            $container->getDefinition('fos_rest.serializer.flatten_exception_normalizer')
                ->replaceArgument(2, $config['exception']['debug']);
            $container->getDefinition('fos_rest.serializer.flatten_exception_normalizer')
                ->replaceArgument(3, 'rfc7807' === $config['exception']['flatten_exception_format']);

            if ($config['exception']['serialize_exceptions']) {
                $container->getDefinition('fos_rest.serializer.exception_normalizer.jms')
                    ->replaceArgument(1, $config['exception']['debug']);
                $container->getDefinition('fos_rest.serializer.exception_normalizer.symfony')
                    ->replaceArgument(1, $config['exception']['debug']);
            } else {
                $container->removeDefinition('fos_rest.serializer.exception_normalizer.jms');
                $container->removeDefinition('fos_rest.serializer.exception_normalizer.symfony');
            }

            if ($config['exception']['serializer_error_renderer']) {
                $format = new Definition();
                $format->setFactory([SerializerErrorRenderer::class, 'getPreferredFormat']);
                $format->setArguments([
                    new Reference('request_stack'),
                ]);
                $debug = new Definition();
                $debug->setFactory([SerializerErrorRenderer::class, 'isDebug']);
                $debug->setArguments([
                    new Reference('request_stack'),
                    '%kernel.debug%',
                ]);
                $container->register('fos_rest.error_renderer.serializer', SerializerErrorRenderer::class)
                    ->setArguments([
                        new Reference('fos_rest.serializer'),
                        $format,
                        new Reference('error_renderer.html', ContainerInterface::NULL_ON_INVALID_REFERENCE),
                        $debug,
                    ]);
                $container->setAlias('error_renderer', 'fos_rest.error_renderer.serializer');
            }
        }
    }

    private function loadSerializer(array $config, ContainerBuilder $container)
    {
        $bodyConverter = $container->hasDefinition('fos_rest.converter.request_body') ? $container->getDefinition('fos_rest.converter.request_body') : null;
        $viewHandler = $container->getDefinition('fos_rest.view_handler.default');
        $options = [];

        if (!empty($config['serializer']['version'])) {
            if ($bodyConverter) {
                $bodyConverter->replaceArgument(2, $config['serializer']['version']);
            }
            $options['exclusionStrategyVersion'] = $config['serializer']['version'];
        }

        if (!empty($config['serializer']['groups'])) {
            if ($bodyConverter) {
                $bodyConverter->replaceArgument(1, $config['serializer']['groups']);
            }
            $options['exclusionStrategyGroups'] = $config['serializer']['groups'];
        }

        $options['serializeNullStrategy'] = $config['serializer']['serialize_null'];
        $viewHandler->replaceArgument(10, $options);
    }

    private function loadZoneMatcherListener(array $config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        if (!empty($config['zone'])) {
            $loader->load('zone_matcher_listener.xml');
            $zoneMatcherListener = $container->getDefinition('fos_rest.zone_matcher_listener');

            foreach ($config['zone'] as $zone) {
                $matcher = $this->createZoneRequestMatcher(
                    $container,
                    $zone['path'],
                    $zone['host'],
                    $zone['methods'],
                    $zone['ips']
                );

                $zoneMatcherListener->addMethodCall('addRequestMatcher', [$matcher]);
            }
        }
    }

    private function createZoneRequestMatcher(ContainerBuilder $container, $path = null, $host = null, $methods = [], $ip = null)
    {
        if ($methods) {
            $methods = array_map('strtoupper', (array) $methods);
        }

        $serialized = serialize([$path, $host, $methods, $ip]);
        $id = 'fos_rest.zone_request_matcher.'.md5($serialized).sha1($serialized);

        // only add arguments that are necessary
        $arguments = [$path, $host, $methods, $ip];
        while (count($arguments) > 0 && !end($arguments)) {
            array_pop($arguments);
        }

        $childDefinitionClass = class_exists(ChildDefinition::class) ? ChildDefinition::class : DefinitionDecorator::class;
        $container
            ->setDefinition($id, new $childDefinitionClass('fos_rest.zone_request_matcher'))
            ->setArguments($arguments)
        ;

        return new Reference($id);
    }
}
