<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\DependencyInjection;

use FOS\RestBundle\DependencyInjection\FOSRestExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * FOSRestExtension test.
 *
 * @author Bulat Shakirzyanov <avalanche123>
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FOSRestExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var FOSRestExtension
     */
    private $extension;

    /**
     * @var bool
     */
    private $includeFormat;

    /**
     * @var array
     */
    private $formats;

    /**
     * @var string
     */
    private $defaultFormat;

    public function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->container->setParameter('kernel.bundles', ['JMSSerializerBundle' => true]);
        $this->extension = new FOSRestExtension();
        $this->includeFormat = true;
        $this->formats = [
            'json' => false,
            'xml' => false,
            'html' => true,
        ];
        $this->defaultFormat = null;
    }

    public function tearDown()
    {
        unset($this->container, $this->extension);
    }

    public function testDisableBodyListener()
    {
        $config = [
            'fos_rest' => ['body_listener' => false],
        ];
        $this->extension->load($config, $this->container);

        $this->assertFalse($this->container->hasDefinition('fos_rest.body_listener'));
    }

    public function testLoadBodyListenerWithDefaults()
    {
        $this->extension->load([], $this->container);
        $decoders = [
            'json' => 'fos_rest.decoder.json',
            'xml' => 'fos_rest.decoder.xml',
        ];

        $this->assertTrue($this->container->hasDefinition('fos_rest.body_listener'));
        $this->assertParameter($decoders, 'fos_rest.decoders');
        $this->assertParameter(false, 'fos_rest.throw_exception_on_unsupported_content_type');
        $this->assertCount(2, $this->container->getDefinition('fos_rest.body_listener')->getArguments());
    }

    public function testLoadBodyListenerWithNormalizerString()
    {
        $config = [
            'fos_rest' => ['body_listener' => [
                'array_normalizer' => 'fos_rest.normalizer.camel_keys',
            ]],
        ];

        $this->extension->load($config, $this->container);
        $normalizerArgument = $this->container->getDefinition('fos_rest.body_listener')->getArgument(2);

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $normalizerArgument);
        $this->assertEquals('fos_rest.normalizer.camel_keys', (string) $normalizerArgument);
    }

    public function testLoadBodyListenerWithNormalizerArray()
    {
        $config = [
            'fos_rest' => ['body_listener' => [
                'array_normalizer' => [
                    'service' => 'fos_rest.normalizer.camel_keys',
                ],
            ]],
        ];

        $this->extension->load($config, $this->container);
        $bodyListener = $this->container->getDefinition('fos_rest.body_listener');
        $normalizerArgument = $bodyListener->getArgument(2);
        $normalizeForms = $bodyListener->getArgument(3);

        $this->assertCount(4, $bodyListener->getArguments());
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $normalizerArgument);
        $this->assertEquals('fos_rest.normalizer.camel_keys', (string) $normalizerArgument);
        $this->assertEquals(false, $normalizeForms);
    }

    public function testLoadBodyListenerWithNormalizerArrayAndForms()
    {
        $config = [
            'fos_rest' => ['body_listener' => [
                'array_normalizer' => [
                    'service' => 'fos_rest.normalizer.camel_keys',
                    'forms' => true,
                ],
            ]],
        ];

        $this->extension->load($config, $this->container);
        $bodyListener = $this->container->getDefinition('fos_rest.body_listener');
        $normalizerArgument = $bodyListener->getArgument(2);
        $normalizeForms = $bodyListener->getArgument(3);

        $this->assertCount(4, $bodyListener->getArguments());
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $normalizerArgument);
        $this->assertEquals('fos_rest.normalizer.camel_keys', (string) $normalizerArgument);
        $this->assertEquals(true, $normalizeForms);
    }

    public function testDisableFormatListener()
    {
        $config = [
            'fos_rest' => ['format_listener' => false],
        ];
        $this->extension->load($config, $this->container);

        $this->assertFalse($this->container->hasDefinition('fos_rest.format_listener'));
    }

    public function testLoadFormatListenerWithDefaults()
    {
        $this->extension->load([], $this->container);

        $this->assertFalse($this->container->hasDefinition('fos_rest.format_listener'));
    }

    public function testLoadFormatListenerWithSingleRule()
    {
        $config = [
            'fos_rest' => ['format_listener' => [
                'rules' => ['path' => '/'],
            ]],
        ];
        $this->extension->load($config, $this->container);

        $this->assertTrue($this->container->hasDefinition('fos_rest.format_listener'));
    }

    public function testLoadParamFetcherListener()
    {
        $config = [
            'fos_rest' => ['param_fetcher_listener' => true],
        ];
        $this->extension->load($config, $this->container);

        $this->assertTrue($this->container->hasDefinition('fos_rest.param_fetcher_listener'));
        $this->assertFalse($this->container->getParameter('fos_rest.param_fetcher_listener.set_params_as_attributes'));
    }

    public function testLoadParamFetcherListenerForce()
    {
        $config = [
            'fos_rest' => ['param_fetcher_listener' => 'force'],
        ];
        $this->extension->load($config, $this->container);

        $this->assertTrue($this->container->hasDefinition('fos_rest.param_fetcher_listener'));
        $this->assertTrue($this->container->getParameter('fos_rest.param_fetcher_listener.set_params_as_attributes'));
    }

    public function testLoadFormatListenerWithMultipleRule()
    {
        $config = [
            'fos_rest' => ['format_listener' => [
                'rules' => [
                    ['path' => '/foo'],
                    ['path' => '/'],
                ],
            ]],
        ];
        $this->extension->load($config, $this->container);

        $this->assertTrue($this->container->hasDefinition('fos_rest.format_listener'));
    }

    public function testLoadFormatListenerMediaType()
    {
        $config = [
            'fos_rest' => ['format_listener' => [
                'rules' => ['path' => '/'],
                'media_type' => true,
            ]],
        ];
        $this->extension->load($config, $this->container);

        $this->assertTrue($this->container->hasDefinition('fos_rest.version_listener'));
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testLoadFormatListenerMediaTypeNoRules()
    {
        $config = [
            'fos_rest' => ['format_listener' => [
                'media_type' => true,
            ]],
        ];
        $this->extension->load($config, $this->container);
    }

    public function testLoadServicesWithDefaults()
    {
        $this->extension->load([], $this->container);

        $this->assertAlias('fos_rest.view_handler.default', 'fos_rest.view_handler');
        $this->assertAlias('fos_rest.view.exception_wrapper_handler', 'fos_rest.exception_handler');
    }

    public function testLoadFormatsWithDefaults()
    {
        $this->extension->load([], $this->container);
        $formats = [
            'json' => false,
            'xml' => false,
            'html' => true,
        ];

        $this->assertEquals($formats, $this->container->getParameter('fos_rest.formats'));
    }

    public function testDisableViewResponseListener()
    {
        $config = [
            'fos_rest' => ['view' => ['view_response_listener' => false]],
        ];
        $this->extension->load($config, $this->container);

        $this->assertFalse($this->container->hasDefinition('fos_rest.view_response_listener'));
    }

    public function testLoadViewResponseListener()
    {
        $config = [
            'fos_rest' => ['view' => ['view_response_listener' => true]],
        ];
        $this->extension->load($config, $this->container);

        $this->assertTrue($this->container->hasDefinition('fos_rest.view_response_listener'));
        $this->assertFalse($this->container->getParameter('fos_rest.view_response_listener.force_view'));
    }

    public function testLoadViewResponseListenerForce()
    {
        $config = [
            'fos_rest' => ['view' => ['view_response_listener' => 'force']],
        ];
        $this->extension->load($config, $this->container);

        $this->assertTrue($this->container->hasDefinition('fos_rest.view_response_listener'));
        $this->assertTrue($this->container->getParameter('fos_rest.view_response_listener.force_view'));
    }

    public function testForceEmptyContentDefault()
    {
        $this->extension->load([], $this->container);
        $this->assertEquals(204, $this->container->getParameter('fos_rest.empty_content'));
    }

    public function testForceEmptyContentIs200()
    {
        $config = ['fos_rest' => ['view' => ['empty_content' => 200]]];
        $this->extension->load($config, $this->container);
        $this->assertEquals(200, $this->container->getParameter('fos_rest.empty_content'));
    }

    public function testViewSerializeNullDefault()
    {
        $this->extension->load([], $this->container);
        $this->assertFalse($this->container->getParameter('fos_rest.serialize_null'));
    }

    public function testViewSerializeNullIsTrue()
    {
        $config = ['fos_rest' => ['view' => ['serialize_null' => true]]];
        $this->extension->load($config, $this->container);
        $this->assertTrue($this->container->getParameter('fos_rest.serialize_null'));
    }

    public function testValidatorAliasWhenEnabled()
    {
        $config = ['fos_rest' => ['body_converter' => ['validate' => true]]];
        $this->extension->load($config, $this->container);
        $this->assertAlias('validator', 'fos_rest.validator');
    }

    public function testValidatorAliasWhenDisabled()
    {
        $config = ['fos_rest' => ['body_converter' => ['validate' => false]]];
        $this->extension->load($config, $this->container);
        $this->assertFalse($this->container->has('fos_rest.validator'));
    }

    /**
     * Test that extension loads properly.
     */
    public function testConfigLoad()
    {
        $controllerLoaderDefinitionName = 'fos_rest.routing.loader.controller';
        $controllerLoaderClass = 'FOS\RestBundle\Routing\Loader\RestRouteLoader';

        $yamlCollectionLoaderDefinitionName = 'fos_rest.routing.loader.yaml_collection';
        $yamlCollectionLoaderClass = 'FOS\RestBundle\Routing\Loader\RestYamlCollectionLoader';

        $xmlCollectionLoaderDefinitionName = 'fos_rest.routing.loader.xml_collection';
        $xmlCollectionLoaderClass = 'FOS\RestBundle\Routing\Loader\RestXmlCollectionLoader';

        $this->extension->load([], $this->container);

        $this->assertTrue($this->container->hasDefinition($controllerLoaderDefinitionName));
        $this->assertValidRestRouteLoader($this->container->getDefinition($controllerLoaderDefinitionName));

        $this->assertTrue($this->container->hasDefinition($yamlCollectionLoaderDefinitionName));
        $this->assertValidRestFileLoader(
            $this->container->getDefinition($yamlCollectionLoaderDefinitionName),
            $this->includeFormat,
            $this->formats,
            $this->defaultFormat
        );

        $this->assertTrue($this->container->hasDefinition($xmlCollectionLoaderDefinitionName));
        $this->assertValidRestFileLoader(
            $this->container->getDefinition($xmlCollectionLoaderDefinitionName),
            $this->includeFormat,
            $this->formats,
            $this->defaultFormat
        );
    }

    public function testContextAdaptersLoad()
    {
        $this->extension->load([], $this->container);

        $this->assertEquals('FOS\RestBundle\Context\Adapter\JMSContextAdapter', $this->container->getParameter('fos_rest.context.adapter.jms_context_adapter.class'));
        $this->assertTrue($this->container->hasDefinition('fos_rest.context.adapter.jms_context_adapter'));

        $this->assertEquals('FOS\RestBundle\Context\Adapter\ArrayContextAdapter', $this->container->getParameter('fos_rest.context.adapter.array_context_adapter.class'));
        $this->assertTrue($this->container->hasDefinition('fos_rest.context.adapter.array_context_adapter'));

        $this->assertEquals('FOS\RestBundle\Context\Adapter\ChainContextAdapter', $this->container->getParameter('fos_rest.context.adapter.chain_context_adapter.class'));
        $this->assertTrue($this->container->hasDefinition('fos_rest.context.adapter.chain_context_adapter'));
        $argument = $this->container->getDefinition('fos_rest.context.adapter.chain_context_adapter')->getArgument(0);
        $this->assertEquals('fos_rest.context.adapter.jms_context_adapter', $argument[0]);
        $this->assertEquals('fos_rest.context.adapter.array_context_adapter', $argument[1]);
    }

    public function testIncludeFormatDisabled()
    {
        $this->extension->load(
            [
                'fos_rest' => [
                    'routing_loader' => [
                        'include_format' => false,
                    ],
                ],
            ],
            $this->container
        );

        $yamlCollectionLoaderDefinitionName = 'fos_rest.routing.loader.yaml_collection';
        $this->assertValidRestFileLoader(
            $this->container->getDefinition($yamlCollectionLoaderDefinitionName),
            false,
            $this->formats,
            $this->defaultFormat
        );

        $xmlCollectionLoaderDefinitionName = 'fos_rest.routing.loader.xml_collection';
        $this->assertValidRestFileLoader(
            $this->container->getDefinition($xmlCollectionLoaderDefinitionName),
            false,
            $this->formats,
            $this->defaultFormat
        );
    }

    public function testDefaultFormat()
    {
        $this->extension->load(
            [
                'fos_rest' => [
                    'routing_loader' => [
                        'default_format' => 'xml',
                    ],
                ],
            ],
            $this->container
        );

        $yamlCollectionLoaderDefinitionName = 'fos_rest.routing.loader.yaml_collection';
        $this->assertValidRestFileLoader(
            $this->container->getDefinition($yamlCollectionLoaderDefinitionName),
            $this->includeFormat,
            $this->formats,
            'xml'
        );

        $xmlCollectionLoaderDefinitionName = 'fos_rest.routing.loader.xml_collection';
        $this->assertValidRestFileLoader(
            $this->container->getDefinition($xmlCollectionLoaderDefinitionName),
            $this->includeFormat,
            $this->formats,
            'xml'
        );
    }

    public function testFormats()
    {
        $this->extension->load(
            [
                'fos_rest' => [
                    'view' => [
                        'formats' => [
                            'json' => false,
                            'xml' => true,
                        ],
                    ],
                ],
            ],
            $this->container
        );

        $yamlCollectionLoaderDefinitionName = 'fos_rest.routing.loader.yaml_collection';
        $this->assertValidRestFileLoader(
            $this->container->getDefinition($yamlCollectionLoaderDefinitionName),
            $this->includeFormat,
            [
                'xml' => false,
                'html' => true,
            ],
            $this->defaultFormat
        );

        $xmlCollectionLoaderDefinitionName = 'fos_rest.routing.loader.xml_collection';
        $this->assertValidRestFileLoader(
            $this->container->getDefinition($xmlCollectionLoaderDefinitionName),
            $this->includeFormat,
            [
                'xml' => false,
                'html' => true,
            ],
            $this->defaultFormat
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testLoadBadClassThrowsException()
    {
        $this->extension->load([
            'fos_rest' => [
                'exception' => [
                    'messages' => [
                        'UnknownException' => true,
                    ],
                ],
            ],
        ], $this->container);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Could not load class 'UnknownException' or the class does not extend from '\Exception'
     */
    public function testLoadBadMessagesClassThrowsException()
    {
        $this->extension->load([
            'fos_rest' => [
                'exception' => [
                    'codes' => [
                        'UnknownException' => 404,
                    ],
                ],
            ],
        ], $this->container);
    }

    public function testLoadOkMessagesClass()
    {
        $this->extension->load([
            'fos_rest' => [
                'exception' => [
                    'codes' => [
                        'Exception' => 404,
                    ],
                ],
            ],
        ], $this->container);
        $this->assertFalse($this->container->hasDefinition('fos_rest.exception.codes'));
    }

    /**
     * @dataProvider getLoadBadCodeValueThrowsExceptionData
     *
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Invalid HTTP code in fos_rest.exception.codes
     */
    public function testLoadBadCodeValueThrowsException($value)
    {
        $this->extension->load([
            'fos_rest' => [
                'exception' => [
                    'codes' => [
                        'Exception' => $value,
                    ],
                ],
            ],
        ], $this->container);
    }

    public function getLoadBadCodeValueThrowsExceptionData()
    {
        $data = [
            null,
            'HTTP_NOT_EXISTS',
            'some random string',
            true,
        ];

        return array_map(function ($i) {
            return [$i];
        }, $data);
    }

    /**
     * Assert that loader definition described properly.
     *
     * @param Definition $loader loader definition
     */
    private function assertValidRestRouteLoader(Definition $loader)
    {
        $arguments = $loader->getArguments();

        $this->assertEquals(5, count($arguments));
        $this->assertEquals('service_container', (string) $arguments[0]);
        $this->assertEquals('file_locator', (string) $arguments[1]);
        $this->assertEquals('controller_name_converter', (string) $arguments[2]);
        $this->assertEquals('fos_rest.routing.loader.reader.controller', (string) $arguments[3]);
        $this->assertEquals('%fos_rest.routing.loader.default_format%', (string) $arguments[4]);
        $this->assertArrayHasKey('routing.loader', $loader->getTags());
    }

    /**
     * Assert that loader definition described properly.
     *
     * @param Definition $loader        loader definition
     * @param bool       $includeFormat whether or not the requested view format must be included in the route path
     * @param string[]   $formats       supported view formats
     * @param string     $defaultFormat default view format
     */
    private function assertValidRestFileLoader(
        Definition $loader,
        $includeFormat,
        array $formats,
        $defaultFormat
    ) {
        $locatorRef = new Reference('file_locator');
        $processorRef = new Reference('fos_rest.routing.loader.processor');
        $arguments = $loader->getArguments();

        $this->assertEquals(5, count($arguments));
        $this->assertEquals($locatorRef, $arguments[0]);
        $this->assertEquals($processorRef, $arguments[1]);
        $this->assertEquals(
            $includeFormat,
            $this->container->getParameter(
                strtr($arguments[2], ['%' => ''])
            )
        );
        $this->assertEquals(
            $formats,
            $this->container->getParameter(
                strtr($arguments[3], ['%' => ''])
            )
        );
        $this->assertEquals(
            $defaultFormat,
            $this->container->getParameter(
                strtr($arguments[4], ['%' => ''])
            )
        );
        $this->assertArrayHasKey('routing.loader', $loader->getTags());
    }

    private function assertAlias($value, $key)
    {
        $this->assertEquals($value, (string) $this->container->getAlias($key), sprintf('%s alias is correct', $key));
    }

    private function assertParameter($value, $key)
    {
        $this->assertEquals($value, $this->container->getParameter($key), sprintf('%s parameter is correct', $key));
    }

    public function testCheckViewHandlerWithJsonp()
    {
        $this->extension->load(['fos_rest' => ['view' => ['jsonp_handler' => null]]], $this->container);

        $this->assertTrue($this->container->has('fos_rest.view_handler'));

        $viewHandler = $this->container->getDefinition('fos_rest.view_handler');
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\DefinitionDecorator', $viewHandler);
    }

    public function testCheckExceptionWrapperHandler()
    {
        $this->extension->load([], $this->container);

        $this->assertTrue($this->container->has('fos_rest.view.exception_wrapper_handler'));
    }

    public function testSerializerExceptionNormalizer()
    {
        $this->extension->load(['fos_rest' => ['view' => true]], $this->container);

        $this->assertTrue($this->container->has('fos_rest.serializer.exception_wrapper_normalizer'));

        $definition = $this->container->getDefinition('fos_rest.serializer.exception_wrapper_normalizer');
        $this->assertEquals('FOS\RestBundle\Serializer\ExceptionWrapperNormalizer', $definition->getClass());
    }
}
