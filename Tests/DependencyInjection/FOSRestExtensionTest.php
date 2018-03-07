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
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

/**
 * FOSRestExtension test.
 *
 * @author Bulat Shakirzyanov <avalanche123>
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FOSRestExtensionTest extends TestCase
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
        $this->container->setParameter('kernel.bundles', array('JMSSerializerBundle' => true));
        $this->container->setParameter('kernel.debug', false);
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
        $this->assertEquals($decoders, $this->container->getDefinition('fos_rest.decoder_provider')->getArgument(1));
        $this->assertFalse($this->container->getDefinition('fos_rest.body_listener')->getArgument(1));
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

        $this->assertInstanceOf(Reference::class, $normalizerArgument);
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
        $this->assertInstanceOf(Reference::class, $normalizerArgument);
        $this->assertEquals('fos_rest.normalizer.camel_keys', (string) $normalizerArgument);
        $this->assertFalse($normalizeForms);
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
        $this->assertInstanceOf(Reference::class, $normalizerArgument);
        $this->assertEquals('fos_rest.normalizer.camel_keys', (string) $normalizerArgument);
        $this->assertTrue($normalizeForms);
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
        $this->assertFalse($this->container->getDefinition('fos_rest.param_fetcher_listener')->getArgument(1));
    }

    public function testLoadParamFetcherListenerForce()
    {
        $config = [
            'fos_rest' => ['param_fetcher_listener' => 'force'],
        ];
        $this->extension->load($config, $this->container);

        $this->assertTrue($this->container->hasDefinition('fos_rest.param_fetcher_listener'));
        $this->assertTrue($this->container->getDefinition('fos_rest.param_fetcher_listener')->getArgument(1));
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

        $viewHandlerAlias = $this->container->getAlias('fos_rest.view_handler');

        $this->assertTrue($viewHandlerAlias->isPublic());

        if (method_exists(Alias::class, 'isPrivate')) {
            $this->assertFalse($viewHandlerAlias->isPrivate());
        }
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
        $this->assertFalse($this->container->getDefinition('fos_rest.view_response_listener')->getArgument(1));
    }

    public function testLoadViewResponseListenerForce()
    {
        $config = [
            'fos_rest' => ['view' => ['view_response_listener' => 'force']],
        ];
        $this->extension->load($config, $this->container);

        $this->assertTrue($this->container->hasDefinition('fos_rest.view_response_listener'));
        $this->assertTrue($this->container->getDefinition('fos_rest.view_response_listener')->getArgument(1));
    }

    public function testForceEmptyContentDefault()
    {
        $this->extension->load([], $this->container);
        $this->assertEquals(204, $this->container->getDefinition('fos_rest.view_handler.default')->getArgument(6));
    }

    public function testForceEmptyContentIs200()
    {
        $config = ['fos_rest' => ['view' => ['empty_content' => 200]]];
        $this->extension->load($config, $this->container);
        $this->assertEquals(200, $this->container->getDefinition('fos_rest.view_handler.default')->getArgument(6));
    }

    public function testViewSerializeNullDefault()
    {
        $this->extension->load([], $this->container);
        $this->assertFalse($this->container->getDefinition('fos_rest.view_handler.default')->getArgument(7));
    }

    public function testViewSerializeNullIsTrue()
    {
        $config = ['fos_rest' => ['view' => ['serialize_null' => true]]];
        $this->extension->load($config, $this->container);
        $this->assertTrue($this->container->getDefinition('fos_rest.view_handler.default')->getArgument(7));
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

    public function testBodyConvertorDisabledAndSerializerVersionGiven()
    {
        $config = ['fos_rest' => ['body_converter' => ['enabled' => false], 'serializer' => ['version' => '1.0']]];
        $this->extension->load($config, $this->container);
    }

    public function testBodyConvertorDisabledAndSerializerGroupsGiven()
    {
        $config = ['fos_rest' => ['body_converter' => ['enabled' => false], 'serializer' => ['groups' => ['Default']]]];
        $this->extension->load($config, $this->container);
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
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
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
     * Test exception.debug config value uses kernel.debug value by default or provided value.
     *
     * @dataProvider getShowExceptionData
     *
     * @param bool        $kernelDebug     kernel.debug param value
     * @param array       $exceptionConfig Exception config
     * @param bool|string $expectedValue   Expected value of show_exception argument
     */
    public function testExceptionDebug($kernelDebug, $exceptionConfig, $expectedValue)
    {
        $this->container->setParameter('kernel.debug', $kernelDebug);
        $extension = new FOSRestExtension();

        $extension->load(array(
            'fos_rest' => array(
                'exception' => $exceptionConfig,
            ),
        ), $this->container);

        $definition = $this->container->getDefinition('fos_rest.exception.controller');
        $this->assertSame($expectedValue, $definition->getArgument(2));

        $definition = $this->container->getDefinition('fos_rest.serializer.exception_normalizer.jms');
        $this->assertSame($expectedValue, $definition->getArgument(1));

        $definition = $this->container->getDefinition('fos_rest.serializer.exception_normalizer.symfony');
        $this->assertSame($expectedValue, $definition->getArgument(1));
    }

    public static function getShowExceptionData()
    {
        return array(
            'empty config, kernel.debug is true' => array(
                true,
                array(),
                true,
            ),
            'empty config, kernel.debug is false' => array(
                false,
                array(),
                false,
            ),
            'config debug true' => array(
                false,
                array('debug' => true),
                true,
            ),
            'config debug false' => array(
                true,
                array('debug' => false),
                false,
            ),
            'config debug null, kernel.debug true' => array(
                false,
                array('debug' => null),
                true,
            ),
            'config debug null, kernel.debug false' => array(
                false,
                array('debug' => null),
                true,
            ),
        );
    }

    public function testGetConfiguration()
    {
        $configuration = $this->extension->getConfiguration(array(), $this->container);

        $this->assertInstanceOf('FOS\RestBundle\DependencyInjection\Configuration', $configuration);
    }

    /**
     * Assert that loader definition described properly.
     *
     * @param Definition $loader loader definition
     */
    private function assertValidRestRouteLoader(Definition $loader)
    {
        $arguments = $loader->getArguments();

        $this->assertCount(5, $arguments);
        $this->assertEquals('service_container', (string) $arguments[0]);
        $this->assertEquals('file_locator', (string) $arguments[1]);
        $this->assertEquals('controller_name_converter', (string) $arguments[2]);
        $this->assertEquals('fos_rest.routing.loader.reader.controller', (string) $arguments[3]);
        $this->assertNull($arguments[4]);
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

        $this->assertCount(5, $arguments);
        $this->assertEquals($locatorRef, $arguments[0]);
        $this->assertEquals($processorRef, $arguments[1]);
        $this->assertSame($includeFormat, $arguments[2]);
        $this->assertEquals($formats, $arguments[3]);
        $this->assertSame($defaultFormat, $arguments[4]);
        $this->assertArrayHasKey('routing.loader', $loader->getTags());
    }

    private function assertAlias($value, $key)
    {
        $this->assertEquals($value, (string) $this->container->getAlias($key), sprintf('%s alias is correct', $key));
    }

    public function testCheckViewHandlerWithJsonp()
    {
        $this->extension->load(['fos_rest' => ['view' => ['jsonp_handler' => null]]], $this->container);

        $this->assertTrue($this->container->has('fos_rest.view_handler'));

        $viewHandler = $this->container->getDefinition('fos_rest.view_handler');

        $childDefinitionClass = class_exists(ChildDefinition::class) ? ChildDefinition::class : DefinitionDecorator::class;
        $this->assertInstanceOf($childDefinitionClass, $viewHandler);
    }

    public function testSerializerExceptionNormalizer()
    {
        $this->extension->load(['fos_rest' => ['exception' => true]], $this->container);

        $this->assertTrue($this->container->has('fos_rest.serializer.exception_normalizer.symfony'));
    }

    public function testZoneMatcherListenerDefault()
    {
        $this->extension->load(array('fos_rest' => array()), $this->container);

        $this->assertFalse($this->container->has('fos_rest.zone_matcher_listener'));
    }

    public function testZoneMatcherListener()
    {
        $config = array('fos_rest' => array(
            'zone' => array(
                'first' => array('path' => '/api/*'),
                'second' => array('path' => '/^second', 'ips' => '127.0.0.1'),
            ),
        ));

        $this->extension->load($config, $this->container);
        $zoneMatcherListener = $this->container->getDefinition('fos_rest.zone_matcher_listener');
        $addRequestMatcherCalls = $zoneMatcherListener->getMethodCalls();

        $this->assertTrue($this->container->has('fos_rest.zone_matcher_listener'));
        $this->assertEquals('FOS\RestBundle\EventListener\ZoneMatcherListener', $zoneMatcherListener->getClass());
        $this->assertCount(2, $addRequestMatcherCalls);

        // First zone
        $this->assertEquals('addRequestMatcher', $addRequestMatcherCalls[0][0]);
        $requestMatcherFirstId = (string) $addRequestMatcherCalls[0][1][0];
        $requestMatcherFirst = $this->container->getDefinition($requestMatcherFirstId);

        $childDefinitionClass = class_exists(ChildDefinition::class) ? ChildDefinition::class : DefinitionDecorator::class;
        $this->assertInstanceOf($childDefinitionClass, $requestMatcherFirst);
        $this->assertEquals('/api/*', $requestMatcherFirst->getArgument(0));

        // Second zone
        $this->assertEquals('addRequestMatcher', $addRequestMatcherCalls[1][0]);
        $requestMatcherSecondId = (string) $addRequestMatcherCalls[1][1][0];
        $requestMatcherSecond = $this->container->getDefinition($requestMatcherSecondId);

        $this->assertInstanceOf($childDefinitionClass, $requestMatcherSecond);
        $this->assertEquals('/^second', $requestMatcherSecond->getArgument(0));
        $this->assertEquals(array('127.0.0.1'), $requestMatcherSecond->getArgument(3));
    }

    public function testMimeTypesArePassedArrays()
    {
        $config = array(
            'fos_rest' => array(
                'view' => array(
                    'mime_types' => array(
                        'json' => array('application/json', 'application/x-json'),
                        'jpg' => 'image/jpeg',
                        'png' => 'image/png',
                    ),
                ),
            ),
        );
        $this->extension->load($config, $this->container);

        $this->assertSame(
            array(
                'json' => array('application/json', 'application/x-json'),
                'jpg' => array('image/jpeg'),
                'png' => array('image/png'),
            ),
            $this->container->getDefinition('fos_rest.mime_type_listener')->getArgument(0)
        );
    }
}
