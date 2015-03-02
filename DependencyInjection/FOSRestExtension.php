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
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use FOS\RestBundle\Util\Codes;

class FOSRestExtension extends Extension implements PrependExtensionInterface
{
    /**
     * Default sensio_framework_extra { view: { annotations: false } }
     *
     * {@inheritDoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        $parameterBag = $container->getParameterBag();
        $configs = $parameterBag->resolveValue($configs);
        $config = $this->processConfiguration(new Configuration(), $configs);

        if (!empty($config['view']['view_response_listener'])) {
            $container->prependExtensionConfig('sensio_framework_extra', array('view' => array('annotations' => false)));
        }
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
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('view.xml');
        $loader->load('routing.xml');
        $loader->load('util.xml');
        $loader->load('request.xml');

        $container->setParameter('fos_rest.cache_dir', $config['cache_dir']);
        $container->setParameter($this->getAlias().'.routing.loader.default_format', $config['routing_loader']['default_format']);
        $container->setParameter($this->getAlias().'.routing.loader.include_format', $config['routing_loader']['include_format']);

        // The validator service alias is only set if validation is enabled for the request body converter
        $validator = $config['service']['validator'];
        unset($config['service']['validator']);

        foreach ($config['service'] as $key => $service) {
            if (null !== $service) {
                $container->setAlias($this->getAlias().'.'.$key, $service);
            }
        }

        $this->loadForm($config, $loader, $container);
        $this->loadSerializer($config, $container);
        $this->loadException($config, $loader, $container);
        $this->loadBodyConverter($config, $validator, $loader, $container);
        $this->loadView($config, $loader, $container);

        $this->loadBodyListener($config, $loader, $container);
        $this->loadFormatListener($config, $loader, $container);
        $this->loadParamFetcherListener($config, $loader, $container);
        $this->loadAllowedMethodsListener($config, $loader);
        $this->loadAccessDeniedListener($config, $loader, $container);
        $this->loadZoneMatcherListener($config, $loader, $container);
    }

    private function loadForm(array $config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        if (!empty($config['disable_csrf_role'])) {
            $loader->load('forms.xml');
            $container->setParameter('fos_rest.disable_csrf_role', $config['disable_csrf_role']);
        }
    }

    private function loadAccessDeniedListener(array $config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        if (!empty($config['access_denied_listener'])) {
            $loader->load('access_denied_listener.xml');
            $container->setParameter($this->getAlias().'.access_denied_listener.formats', $config['access_denied_listener']);
            $container->setParameter($this->getAlias().'.access_denied_listener.unauthorized_challenge', $config['unauthorized_challenge']);
        }
    }

    public function loadAllowedMethodsListener(array $config, XmlFileLoader $loader)
    {
        if (!empty($config['allowed_methods_listener'])) {
            $loader->load('allowed_methods_listener.xml');
        }
    }

    private function loadBodyListener(array $config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        if (!empty($config['body_listener'])) {
            $loader->load('body_listener.xml');

            $container->setParameter($this->getAlias().'.throw_exception_on_unsupported_content_type', $config['body_listener']['throw_exception_on_unsupported_content_type']);
            $container->setParameter($this->getAlias().'.body_default_format', $config['body_listener']['default_format']);
            $container->setParameter($this->getAlias().'.decoders', $config['body_listener']['decoders']);

            $arrayNormalizer = $config['body_listener']['array_normalizer'];

            if (null !== $arrayNormalizer['service']) {
                $bodyListener = $container->getDefinition($this->getAlias().'.body_listener');
                $bodyListener->addArgument(new Reference($arrayNormalizer['service']));
                $bodyListener->addArgument($arrayNormalizer['forms']);
            }
        }
    }

    private function loadFormatListener(array $config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        if (!empty($config['format_listener']['rules'])) {
            $loader->load('format_listener.xml');

            foreach ($config['format_listener']['rules'] as $key => $rule) {
                if (!isset($rule['exception_fallback_format'])) {
                    $config['format_listener']['rules'][$key]['exception_fallback_format'] = $rule['fallback_format'];
                }
            }

            $container->setParameter(
                $this->getAlias().'.format_listener.rules',
                $config['format_listener']['rules']
            );

            if (!empty($config['format_listener']['media_type']['version_regex'])) {
                $container->setParameter(
                    $this->getAlias().'.format_listener.media_type.version_regex',
                    $config['format_listener']['media_type']['version_regex']
                );
            } else {
                $container->removeDefinition('fos_rest.version_listener');
            }
        }
    }

    private function loadParamFetcherListener(array $config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        if (!empty($config['param_fetcher_listener'])) {
            $loader->load('param_fetcher_listener.xml');

            if ('force' === $config['param_fetcher_listener']) {
                $container->setParameter($this->getAlias().'.param_fetcher_listener.set_params_as_attributes', true);
            }
        }
    }

    private function loadBodyConverter(array $config, $validator, XmlFileLoader $loader, ContainerBuilder $container)
    {
        if (!empty($config['body_converter'])) {
            if (!empty($config['body_converter']['enabled'])) {
                $loader->load('request_body_param_converter.xml');
            }

            if (!empty($config['body_converter']['validate'])) {
                $container->setAlias($this->getAlias().'.validator', $validator);
            }

            if (!empty($config['body_converter']['validation_errors_argument'])) {
                $container->setParameter(
                    'fos_rest.converter.request_body.validation_errors_argument',
                    $config['body_converter']['validation_errors_argument']
                );
            }
        }
    }

    private function loadView(array $config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        if (!empty($config['view']['exception_wrapper_handler'])) {
            $container->setParameter($this->getAlias().'.view.exception_wrapper_handler', $config['view']['exception_wrapper_handler']);
        }

        if (!empty($config['view']['jsonp_handler'])) {
            $handler = new DefinitionDecorator($config['service']['view_handler']);
            $handler->setPublic(true);

            $jsonpHandler = new Reference($this->getAlias().'.view_handler.jsonp');
            $handler->addMethodCall('registerHandler', array('jsonp', array($jsonpHandler, 'createResponse')));
            $container->setDefinition($this->getAlias().'.view_handler', $handler);

            $container->setParameter($this->getAlias().'.view_handler.jsonp.callback_param', $config['view']['jsonp_handler']['callback_param']);

            if ('/(^[a-z0-9_]+$)|(^YUI\.Env\.JSONP\._[0-9]+$)/i' !== $config['view']['jsonp_handler']['callback_filter']) {
                throw new \LogicException('As of 1.2.0, the "callback_filter" parameter is deprecated, and is not used anymore. For more information, read: https://github.com/FriendsOfSymfony/FOSRestBundle/pull/642.');
            }

            if (empty($config['view']['mime_types']['jsonp'])) {
                $config['view']['mime_types']['jsonp'] = $config['view']['jsonp_handler']['mime_type'];
            }
        }

        if (!empty($config['view']['mime_types'])) {
            $loader->load('mime_type_listener.xml');

            $container->setParameter($this->getAlias().'.mime_types', $config['view']['mime_types']);
        } else {
            $container->setParameter($this->getAlias().'.mime_types', array());
        }

        if (!empty($config['view']['view_response_listener'])) {
            $loader->load('view_response_listener.xml');
            $container->setParameter($this->getAlias().'.view_response_listener.force_view', 'force' === $config['view']['view_response_listener']);
        }

        $formats = array();
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

        $container->setParameter($this->getAlias().'.formats', $formats);

        foreach ($config['view']['force_redirects'] as $format => $code) {
            if (true === $code) {
                $config['view']['force_redirects'][$format] = Codes::HTTP_FOUND;
            }
        }

        $container->setParameter($this->getAlias().'.force_redirects', $config['view']['force_redirects']);

        if (!is_numeric($config['view']['failed_validation'])) {
            $config['view']['failed_validation'] = constant('\FOS\RestBundle\Util\Codes::'.$config['view']['failed_validation']);
        }

        $container->setParameter($this->getAlias().'.failed_validation', $config['view']['failed_validation']);

        if (!is_numeric($config['view']['empty_content'])) {
            $config['view']['empty_content'] = constant('\FOS\RestBundle\Util\Codes::'.$config['view']['empty_content']);
        }

        $container->setParameter($this->getAlias().'.empty_content', $config['view']['empty_content']);
        $container->setParameter($this->getAlias().'.serialize_null', $config['view']['serialize_null']);
        $container->setParameter($this->getAlias().'.default_engine', $config['view']['default_engine']);
    }

    private function loadException(array $config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        if ($config['exception']['enabled']) {
            $loader->load('exception_listener.xml');

            if ($config['exception']['exception_controller']) {
                $container->setParameter('fos_rest.exception_listener.controller', $config['exception']['exception_controller']);
            }
        }

        foreach ($config['exception']['codes'] as $exception => $code) {
            if (!is_numeric($code)) {
                $config['exception']['codes'][$exception] = constant("\FOS\RestBundle\Util\Codes::$code");
            }

            $this->testExceptionExists($exception);
        }

        foreach ($config['exception']['messages'] as $exception => $message) {
            $this->testExceptionExists($exception);
        }

        $container->setParameter($this->getAlias().'.exception.codes', $config['exception']['codes']);
        $container->setParameter($this->getAlias().'.exception.messages', $config['exception']['messages']);
    }

    private function loadSerializer(array $config, ContainerBuilder $container)
    {
        if (!empty($config['serializer']['version'])) {
            $container->setParameter($this->getAlias().'.serializer.exclusion_strategy.version', $config['serializer']['version']);
        }

        if (!empty($config['serializer']['groups'])) {
            $container->setParameter($this->getAlias().'.serializer.exclusion_strategy.groups', $config['serializer']['groups']);
        }

        $container->setParameter($this->getAlias().'.serializer.serialize_null', $config['serializer']['serialize_null']);
    }

    private function loadZoneMatcherListener(array $config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        $loader->load('zone_matcher_listener.xml');

        if (!empty($config['zone'])) {
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
            ->register($id, '%fos_rest.zone_request_matcher.class%')
            ->setPublic(false)
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
        if (!is_subclass_of($exception, '\Exception') && !is_a($exception, '\Exception', true)) {
            throw new \InvalidArgumentException("FOSRestBundle exception mapper: Could not load class '$exception' or the class does not extend from '\Exception'. Most probably this is a configuration problem.");
        }
    }
}
