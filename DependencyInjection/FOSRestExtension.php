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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\HttpFoundation\Response;

class FOSRestExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration($container->getParameter('kernel.debug'));
    }

    /**
     * Loads the services based on your application configuration.
     *
     * @param array            $configs
     * @param ContainerBuilder $container
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration($container->getParameter('kernel.debug'));
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('view.xml');
        $loader->load('routing.xml');
        $loader->load('request.xml');
        $loader->load('serializer.xml');

        $container->getDefinition('fos_rest.routing.loader.controller')->replaceArgument(4, $config['routing_loader']['default_format']);
        $container->getDefinition('fos_rest.routing.loader.yaml_collection')->replaceArgument(4, $config['routing_loader']['default_format']);
        $container->getDefinition('fos_rest.routing.loader.xml_collection')->replaceArgument(4, $config['routing_loader']['default_format']);

        $container->getDefinition('fos_rest.routing.loader.yaml_collection')->replaceArgument(2, $config['routing_loader']['include_format']);
        $container->getDefinition('fos_rest.routing.loader.xml_collection')->replaceArgument(2, $config['routing_loader']['include_format']);
        $container->getDefinition('fos_rest.routing.loader.reader.action')->replaceArgument(3, $config['routing_loader']['include_format']);

        // The validator service alias is only set if validation is enabled for the request body converter
        $validator = $config['service']['validator'];
        unset($config['service']['validator']);

        foreach ($config['service'] as $key => $service) {
            if (null !== $service) {
                $container->setAlias('fos_rest.'.$key, $service);
            }
        }

        $this->loadForm($config, $loader, $container);
        $this->loadException($config, $loader, $container);
        $this->loadBodyConverter($config, $validator, $loader, $container);
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
            $container->getDefinition('fos_rest.form.extension.csrf_disable')->replaceArgument(1, $config['disable_csrf_role']);
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
            $service->addMethodCall('setDefaultFormat', array($config['body_listener']['default_format']));

            $container->getDefinition('fos_rest.decoder_provider')->replaceArgument(1, $config['body_listener']['decoders']);

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
            $versionListener->replaceArgument(2, $config['versioning']['default_version']);

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

    private function loadBodyConverter(array $config, $validator, XmlFileLoader $loader, ContainerBuilder $container)
    {
        if (empty($config['body_converter'])) {
            return;
        }

        if (!empty($config['body_converter']['enabled'])) {
            $loader->load('request_body_param_converter.xml');

            if (!empty($config['body_converter']['validation_errors_argument'])) {
                $container->getDefinition('fos_rest.converter.request_body')->replaceArgument(4, $config['body_converter']['validation_errors_argument']);
            }
        }

        if (!empty($config['body_converter']['validate'])) {
            $container->setAlias('fos_rest.validator', $validator);
        }
    }

    private function loadView(array $config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        if (!empty($config['view']['jsonp_handler'])) {
            $handler = new DefinitionDecorator($config['service']['view_handler']);
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

        $container->getDefinition('fos_rest.routing.loader.yaml_collection')->replaceArgument(3, $formats);
        $container->getDefinition('fos_rest.routing.loader.xml_collection')->replaceArgument(3, $formats);
        $container->getDefinition('fos_rest.routing.loader.reader.action')->replaceArgument(4, $formats);

        foreach ($config['view']['force_redirects'] as $format => $code) {
            if (true === $code) {
                $config['view']['force_redirects'][$format] = Response::HTTP_FOUND;
            }
        }

        if (!is_numeric($config['view']['failed_validation'])) {
            $config['view']['failed_validation'] = constant('\Symfony\Component\HttpFoundation\Response::'.$config['view']['failed_validation']);
        }

        $defaultViewHandler = $container->getDefinition('fos_rest.view_handler.default');
        $defaultViewHandler->replaceArgument(4, $formats);
        $defaultViewHandler->replaceArgument(5, $config['view']['failed_validation']);

        if (!is_numeric($config['view']['empty_content'])) {
            $config['view']['empty_content'] = constant('\Symfony\Component\HttpFoundation\Response::'.$config['view']['empty_content']);
        }

        $defaultViewHandler->replaceArgument(6, $config['view']['empty_content']);
        $defaultViewHandler->replaceArgument(7, $config['view']['serialize_null']);
        $defaultViewHandler->replaceArgument(8, $config['view']['force_redirects']);
        $defaultViewHandler->replaceArgument(9, $config['view']['default_engine']);
    }

    private function loadException(array $config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        if ($config['exception']['enabled']) {
            $loader->load('exception_listener.xml');

            if (!empty($config['exception']['service'])) {
                $service = $container->getDefinition('fos_rest.exception_listener');
                $service->clearTag('kernel.event_subscriber');
            }

            if ($config['exception']['exception_controller']) {
                $container->getDefinition('fos_rest.exception_listener')->replaceArgument(0, $config['exception']['exception_controller']);
            } elseif (isset($container->getParameter('kernel.bundles')['TwigBundle'])) {
                $container->getDefinition('fos_rest.exception_listener')->replaceArgument(0, 'fos_rest.exception.twig_controller:showAction');
            }

            $container->getDefinition('fos_rest.exception.codes_map')
                ->replaceArgument(0, $config['exception']['codes']);
            $container->getDefinition('fos_rest.exception.messages_map')
                ->replaceArgument(0, $config['exception']['messages']);

            $container->getDefinition('fos_rest.exception.controller')
                ->replaceArgument(2, $config['exception']['debug']);
            $container->getDefinition('fos_rest.serializer.exception_normalizer.jms')
                ->replaceArgument(1, $config['exception']['debug']);
            $container->getDefinition('fos_rest.serializer.exception_normalizer.symfony')
                ->replaceArgument(1, $config['exception']['debug']);
        }

        foreach ($config['exception']['codes'] as $exception => $code) {
            if (!is_numeric($code)) {
                $config['exception']['codes'][$exception] = constant("\Symfony\Component\HttpFoundation\Response::$code");
            }

            $this->testExceptionExists($exception);
        }

        foreach ($config['exception']['messages'] as $exception => $message) {
            $this->testExceptionExists($exception);
        }
    }

    private function loadSerializer(array $config, ContainerBuilder $container)
    {
        $bodyConverter = $container->hasDefinition('fos_rest.converter.request_body') ? $container->getDefinition('fos_rest.converter.request_body') : null;
        $viewHandler = $container->getDefinition('fos_rest.view_handler.default');

        if (!empty($config['serializer']['version'])) {
            if ($bodyConverter) {
                $bodyConverter->replaceArgument(2, $config['serializer']['version']);
            }
            $viewHandler->addMethodCall('setExclusionStrategyVersion', array($config['serializer']['version']));
        }

        if (!empty($config['serializer']['groups'])) {
            if ($bodyConverter) {
                $bodyConverter->replaceArgument(1, $config['serializer']['groups']);
            }
            $viewHandler->addMethodCall('setExclusionStrategyGroups', array($config['serializer']['groups']));
        }

        $viewHandler->addMethodCall('setSerializeNullStrategy', array($config['serializer']['serialize_null']));
    }

    private function loadZoneMatcherListener(array $config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        if (!empty($config['zone'])) {
            $loader->load('zone_matcher_listener.xml');
            $zoneMatcherListener = $container->getDefinition('fos_rest.zone_matcher_listener');

            foreach ($config['zone'] as $zone) {
                $matcher = $this->createZoneRequestMatcher($container,
                    $zone['path'],
                    $zone['host'],
                    $zone['methods'],
                    $zone['ips']
                );

                $zoneMatcherListener->addMethodCall('addRequestMatcher', array($matcher));
            }
        }
    }

    private function createZoneRequestMatcher(ContainerBuilder $container, $path = null, $host = null, $methods = array(), $ip = null)
    {
        if ($methods) {
            $methods = array_map('strtoupper', (array) $methods);
        }

        $serialized = serialize(array($path, $host, $methods, $ip));
        $id = 'fos_rest.zone_request_matcher.'.md5($serialized).sha1($serialized);

        // only add arguments that are necessary
        $arguments = array($path, $host, $methods, $ip);
        while (count($arguments) > 0 && !end($arguments)) {
            array_pop($arguments);
        }

        $container
            ->setDefinition($id, new DefinitionDecorator('fos_rest.zone_request_matcher'))
            ->setArguments($arguments)
        ;

        return new Reference($id);
    }

    /**
     * Checks if an exception is loadable.
     *
     * @param string $exception Class to test
     *
     * @throws \InvalidArgumentException if the class was not found.
     */
    private function testExceptionExists($exception)
    {
        if (!is_subclass_of($exception, \Exception::class) && !is_a($exception, \Exception::class, true)) {
            throw new \InvalidArgumentException("FOSRestBundle exception mapper: Could not load class '$exception' or the class does not extend from '\Exception'. Most probably this is a configuration problem.");
        }
    }
}
