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

        if ($config['view']['view_response_listener']['enabled']) {
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
        $container->setParameter('fos_rest.routing.loader.default_format', $config['routing_loader']['default_format']);
        $container->setParameter('fos_rest.routing.loader.include_format', $config['routing_loader']['include_format']);

        // The validator service alias is only set if validation is enabled for the request body converter
        $validator = $config['service']['validator'];
        unset($config['service']['validator']);

        foreach ($config['service'] as $key => $service) {
            if (null !== $service) {
                $container->setAlias('fos_rest.'.$key, $service);
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
        $this->loadAllowedMethodsListener($config, $loader, $container);
        $this->loadAccessDeniedListener($config, $loader, $container);
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
        if ($config['access_denied_listener']['enabled'] && !empty($config['access_denied_listener']['formats'])) {
            $loader->load('access_denied_listener.xml');

            if (!empty($config['access_denied_listener']['service'])) {
                $service = $container->getDefinition('fos_rest.access_denied_listener');
                $service->clearTag('kernel.event_listener');
            }

            $container->setParameter('fos_rest.access_denied_listener.formats', $config['access_denied_listener']['formats']);
            $container->setParameter('fos_rest.access_denied_listener.unauthorized_challenge', $config['unauthorized_challenge']);
        }
    }

    public function loadAllowedMethodsListener(array $config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        if ($config['allowed_methods_listener']['enabled']) {

            if (!empty($config['allowed_methods_listener']['service'])) {
                $service = $container->getDefinition('fos_rest.allowed_methods_listener');
                $service->clearTag('kernel.event_listener');
            }

            $loader->load('allowed_methods_listener.xml');
        }
    }

    private function loadBodyListener(array $config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        if ($config['body_listener']['enabled']) {
            $loader->load('body_listener.xml');

            if (!empty($config['body_listener']['service'])) {
                $service = $container->getDefinition('fos_rest.body_listener');
                $service->clearTag('kernel.event_listener');
            }

            $container->setParameter('fos_rest.throw_exception_on_unsupported_content_type', $config['body_listener']['throw_exception_on_unsupported_content_type']);
            $container->setParameter('fos_rest.body_default_format', $config['body_listener']['default_format']);
            $container->setParameter('fos_rest.decoders', $config['body_listener']['decoders']);

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

            foreach ($config['format_listener']['rules'] as $key => $rule) {
                if (!isset($rule['exception_fallback_format'])) {
                    $config['format_listener']['rules'][$key]['exception_fallback_format'] = $rule['fallback_format'];
                }
            }

            $container->setParameter(
                'fos_rest.format_listener.rules',
                $config['format_listener']['rules']
            );

            if (!empty($config['format_listener']['media_type']['enabled']) && !empty($config['format_listener']['media_type']['version_regex'])) {
                $container->setParameter(
                    'fos_rest.format_listener.media_type.version_regex',
                    $config['format_listener']['media_type']['version_regex']
                );

                if (!empty($config['format_listener']['media_type']['service'])) {
                    $service = $container->getDefinition('fos_rest.version_listener');
                    $service->clearTag('kernel.event_listener');
                }
            } else {
                $container->removeDefinition('fos_rest.version_listener');
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
                $container->setParameter('fos_rest.param_fetcher_listener.set_params_as_attributes', true);
            }
        }
    }

    private function loadBodyConverter(array $config, $validator, XmlFileLoader $loader, ContainerBuilder $container)
    {
        if (!empty($config['body_converter'])) {
            if (!empty($config['body_converter']['enabled'])) {
                $parameter = new \ReflectionParameter(
                    array(
                        'Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface',
                        'supports',
                    ),
                    'configuration'
                );

                if ('Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter' === $parameter->getClass()->getName()) {
                    $container->setParameter(
                        'fos_rest.converter.request_body.class',
                        'FOS\RestBundle\Request\RequestBodyParamConverter'
                    );
                } else {
                    $container->setParameter(
                        'fos_rest.converter.request_body.class',
                        'FOS\RestBundle\Request\RequestBodyParamConverter20'
                    );
                }

                $loader->load('request_body_param_converter.xml');
            }

            if (!empty($config['body_converter']['validate'])) {
                $container->setAlias('fos_rest.validator', $validator);
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
            $container->setParameter('fos_rest.view.exception_wrapper_handler', $config['view']['exception_wrapper_handler']);
        }

        if (!empty($config['view']['jsonp_handler'])) {
            $handler = new DefinitionDecorator($config['service']['view_handler']);
            $handler->setPublic(true);

            $jsonpHandler = new Reference('fos_rest.view_handler.jsonp');
            $handler->addMethodCall('registerHandler', array('jsonp', array($jsonpHandler, 'createResponse')));
            $container->setDefinition('fos_rest.view_handler', $handler);

            $container->setParameter('fos_rest.view_handler.jsonp.callback_param', $config['view']['jsonp_handler']['callback_param']);

            if ('/(^[a-z0-9_]+$)|(^YUI\.Env\.JSONP\._[0-9]+$)/i' !== $config['view']['jsonp_handler']['callback_filter']) {
                throw new \LogicException('As of 1.2.0, the "callback_filter" parameter is deprecated, and is not used anymore. For more information, read: https://github.com/FriendsOfSymfony/FOSRestBundle/pull/642.');
            }

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

            $container->setParameter('fos_rest.mime_types', $config['view']['mime_types']);
        } else {
            $container->setParameter('fos_rest.mime_types', array());
        }

        if ($config['view']['view_response_listener']['enabled']) {
            $loader->load('view_response_listener.xml');

            if (!empty($config['view_response_listener']['service'])) {
                $service = $container->getDefinition('fos_rest.view_response_listener');
                $service->clearTag('kernel.event_listener');
            }

            $container->setParameter('fos_rest.view_response_listener.force_view', $config['view']['view_response_listener']['force']);
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

        $container->setParameter('fos_rest.formats', $formats);

        foreach ($config['view']['force_redirects'] as $format => $code) {
            if (true === $code) {
                $config['view']['force_redirects'][$format] = Codes::HTTP_FOUND;
            }
        }

        $container->setParameter('fos_rest.force_redirects', $config['view']['force_redirects']);

        if (!is_numeric($config['view']['failed_validation'])) {
            $config['view']['failed_validation'] = constant('\FOS\RestBundle\Util\Codes::'.$config['view']['failed_validation']);
        }

        $container->setParameter('fos_rest.failed_validation', $config['view']['failed_validation']);

        if (!is_numeric($config['view']['empty_content'])) {
            $config['view']['empty_content'] = constant('\FOS\RestBundle\Util\Codes::'.$config['view']['empty_content']);
        }

        $container->setParameter('fos_rest.empty_content', $config['view']['empty_content']);
        $container->setParameter('fos_rest.serialize_null', $config['view']['serialize_null']);
        $container->setParameter('fos_rest.default_engine', $config['view']['default_engine']);
    }

    private function loadException(array $config, XmlFileLoader $loader, ContainerBuilder $container)
    {
        if ($config['exception']['enabled']) {
            $loader->load('exception_listener.xml');

            if (!empty($config['exception']['service'])) {
                $service = $container->getDefinition('fos_rest.exception_listener');
                $service->clearTag('kernel.event_listener');
            }

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

        $container->setParameter('fos_rest.exception.codes', $config['exception']['codes']);
        $container->setParameter('fos_rest.exception.messages', $config['exception']['messages']);
    }

    private function loadSerializer(array $config, ContainerBuilder $container)
    {
        if (!empty($config['serializer']['version'])) {
            $container->setParameter('fos_rest.serializer.exclusion_strategy.version', $config['serializer']['version']);
        }

        if (!empty($config['serializer']['groups'])) {
            $container->setParameter('fos_rest.serializer.exclusion_strategy.groups', $config['serializer']['groups']);
        }

        $container->setParameter('fos_rest.serializer.serialize_null', $config['serializer']['serialize_null']);
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
