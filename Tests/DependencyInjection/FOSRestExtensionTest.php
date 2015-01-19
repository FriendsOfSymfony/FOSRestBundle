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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use FOS\RestBundle\DependencyInjection\FOSRestExtension;

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
        $this->container->setParameter('kernel.bundles', array('JMSSerializerBundle' => true));
        $this->extension = new FOSRestExtension();
        $this->includeFormat = true;
        $this->formats = array(
            'json' => false,
            'xml'  => false,
            'html' => true,
        );
        $this->defaultFormat = null;
    }

    public function tearDown()
    {
        unset($this->container, $this->extension);
    }

    public function testDisableBodyListener()
    {
        $config = array(
            'fos_rest' => array('body_listener' => false),
        );
        $this->extension->load($config, $this->container);

        $this->assertFalse($this->container->hasDefinition('fos_rest.body_listener'));
    }

    public function testLoadBodyListenerWithDefaults()
    {
        $this->extension->load(array(), $this->container);
        $decoders = array(
            'json' => 'fos_rest.decoder.json',
            'xml' => 'fos_rest.decoder.xml',
        );

        $this->assertTrue($this->container->hasDefinition('fos_rest.body_listener'));
        $this->assertParameter($decoders, 'fos_rest.decoders');
        $this->assertParameter(false, 'fos_rest.throw_exception_on_unsupported_content_type');
        $this->assertCount(2, $this->container->getDefinition('fos_rest.body_listener')->getArguments());
    }

    public function testLoadBodyListenerWithNormalizerString()
    {
        $config = array(
            'fos_rest' => array('body_listener' => array(
                'array_normalizer' => 'fos_rest.normalizer.camel_keys',
            )),
        );

        $this->extension->load($config, $this->container);
        $normalizerArgument = $this->container->getDefinition('fos_rest.body_listener')->getArgument(2);

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $normalizerArgument);
        $this->assertEquals('fos_rest.normalizer.camel_keys', (string) $normalizerArgument);
    }

    public function testLoadBodyListenerWithNormalizerArray()
    {
        $config = array(
            'fos_rest' => array('body_listener' => array(
                'array_normalizer' => array(
                    'service' => 'fos_rest.normalizer.camel_keys',
                )
            )),
        );

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
        $config = array(
            'fos_rest' => array('body_listener' => array(
                'array_normalizer' => array(
                    'service' => 'fos_rest.normalizer.camel_keys',
                    'forms' => true,
                )
            )),
        );

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
        $config = array(
            'fos_rest' => array('format_listener' => false),
        );
        $this->extension->load($config, $this->container);

        $this->assertFalse($this->container->hasDefinition('fos_rest.format_listener'));
    }

    public function testLoadFormatListenerWithDefaults()
    {
        $this->extension->load(array(), $this->container);

        $this->assertFalse($this->container->hasDefinition('fos_rest.format_listener'));
    }

    public function testLoadServicesWithDefaults()
    {
        $this->extension->load(array(), $this->container);

        $this->assertAlias('fos_rest.view_handler.default', 'fos_rest.view_handler');
        $this->assertAlias('fos_rest.view.exception_wrapper_handler', 'fos_rest.exception_handler');
    }

    public function testLoadFormatsWithDefaults()
    {
        $this->extension->load(array(), $this->container);
        $formats = array(
            'json' => false,
            'xml' => false,
            'html' => true,
        );

        $this->assertEquals($formats, $this->container->getParameter('fos_rest.formats'));
    }

    public function testDisableViewResponseListener()
    {
        $config = array(
            'fos_rest' => array('view' => array('view_response_listener' => false)),
        );
        $this->extension->load($config, $this->container);

        $this->assertFalse($this->container->hasDefinition('fos_rest.view_response_listener'));
    }

    public function testForceEmptyContentDefault()
    {
        $this->extension->load(array(), $this->container);
        $this->assertEquals(204, $this->container->getParameter('fos_rest.empty_content'));
    }

    public function testForceEmptyContentIs200()
    {
        $config = array('fos_rest' => array('view' => array('empty_content' => 200)));
        $this->extension->load($config, $this->container);
        $this->assertEquals(200, $this->container->getParameter('fos_rest.empty_content'));
    }

    public function testViewSerializeNullDefault()
    {
        $this->extension->load(array(), $this->container);
        $this->assertFalse($this->container->getParameter('fos_rest.serialize_null'));
    }

    public function testViewSerializeNullIsTrue()
    {
        $config = array('fos_rest' => array('view' => array('serialize_null' => true)));
        $this->extension->load($config, $this->container);
        $this->assertTrue($this->container->getParameter('fos_rest.serialize_null'));
    }

    public function testValidatorAliasWhenEnabled()
    {
        $config = array('fos_rest' => array('body_converter' => array('validate' => true)));
        $this->extension->load($config, $this->container);
        $this->assertAlias('validator', 'fos_rest.validator');
    }

    public function testValidatorAliasWhenDisabled()
    {
        $config = array('fos_rest' => array('body_converter' => array('validate' => false)));
        $this->extension->load($config, $this->container);
        $this->assertFalse($this->container->has('fos_rest.validator'));
    }

    /**
     * Test that extension loads properly.
     */
    public function testConfigLoad()
    {
        $controllerLoaderDefinitionName = 'fos_rest.routing.loader.controller';
        $controllerLoaderClassParameter = 'fos_rest.routing.loader.controller.class';
        $controllerLoaderClass          = 'FOS\RestBundle\Routing\Loader\RestRouteLoader';

        $yamlCollectionLoaderDefinitionName = 'fos_rest.routing.loader.yaml_collection';
        $yamlCollectionLoaderClassParameter = 'fos_rest.routing.loader.yaml_collection.class';
        $yamlCollectionLoaderClass          = 'FOS\RestBundle\Routing\Loader\RestYamlCollectionLoader';

        $xmlCollectionLoaderDefinitionName  = 'fos_rest.routing.loader.xml_collection';
        $xmlCollectionLoaderClassParameter  = 'fos_rest.routing.loader.xml_collection.class';
        $xmlCollectionLoaderClass           = 'FOS\RestBundle\Routing\Loader\RestXmlCollectionLoader';

        $this->extension->load(array(), $this->container);

        $this->assertEquals($controllerLoaderClass, $this->container->getParameter($controllerLoaderClassParameter));
        $this->assertTrue($this->container->hasDefinition($controllerLoaderDefinitionName));
        $this->assertValidRestRouteLoader(
            $this->container->getDefinition($controllerLoaderDefinitionName),
            $controllerLoaderClassParameter
        );

        $this->assertEquals($yamlCollectionLoaderClass, $this->container->getParameter($yamlCollectionLoaderClassParameter));
        $this->assertTrue($this->container->hasDefinition($yamlCollectionLoaderDefinitionName));
        $this->assertValidRestFileLoader(
            $this->container->getDefinition($yamlCollectionLoaderDefinitionName),
            $yamlCollectionLoaderClassParameter,
            $this->includeFormat,
            $this->formats,
            $this->defaultFormat
        );

        $this->assertEquals($xmlCollectionLoaderClass, $this->container->getParameter($xmlCollectionLoaderClassParameter));
        $this->assertTrue($this->container->hasDefinition($xmlCollectionLoaderDefinitionName));
        $this->assertValidRestFileLoader(
            $this->container->getDefinition($xmlCollectionLoaderDefinitionName),
            $xmlCollectionLoaderClassParameter,
            $this->includeFormat,
            $this->formats,
            $this->defaultFormat
        );
    }

    public function testIncludeFormatDisabled()
    {
        $this->extension->load(
            array(
                'fos_rest' => array(
                    'routing_loader' => array(
                        'include_format' => false,
                    ),
                ),
            ),
            $this->container
        );

        $yamlCollectionLoaderDefinitionName = 'fos_rest.routing.loader.yaml_collection';
        $yamlCollectionLoaderClassParameter = 'fos_rest.routing.loader.yaml_collection.class';
        $this->assertValidRestFileLoader(
            $this->container->getDefinition($yamlCollectionLoaderDefinitionName),
            $yamlCollectionLoaderClassParameter,
            false,
            $this->formats,
            $this->defaultFormat
        );

        $xmlCollectionLoaderDefinitionName  = 'fos_rest.routing.loader.xml_collection';
        $xmlCollectionLoaderClassParameter  = 'fos_rest.routing.loader.xml_collection.class';
        $this->assertValidRestFileLoader(
            $this->container->getDefinition($xmlCollectionLoaderDefinitionName),
            $xmlCollectionLoaderClassParameter,
            false,
            $this->formats,
            $this->defaultFormat
        );
    }

    public function testDefaultFormat()
    {
        $this->extension->load(
            array(
                'fos_rest' => array(
                    'routing_loader' => array(
                        'default_format' => 'xml',
                    ),
                ),
            ),
            $this->container
        );

        $yamlCollectionLoaderDefinitionName = 'fos_rest.routing.loader.yaml_collection';
        $yamlCollectionLoaderClassParameter = 'fos_rest.routing.loader.yaml_collection.class';
        $this->assertValidRestFileLoader(
            $this->container->getDefinition($yamlCollectionLoaderDefinitionName),
            $yamlCollectionLoaderClassParameter,
            $this->includeFormat,
            $this->formats,
            'xml'
        );

        $xmlCollectionLoaderDefinitionName  = 'fos_rest.routing.loader.xml_collection';
        $xmlCollectionLoaderClassParameter  = 'fos_rest.routing.loader.xml_collection.class';
        $this->assertValidRestFileLoader(
            $this->container->getDefinition($xmlCollectionLoaderDefinitionName),
            $xmlCollectionLoaderClassParameter,
            $this->includeFormat,
            $this->formats,
            'xml'
        );
    }

    public function testFormats()
    {
        $this->extension->load(
            array(
                'fos_rest' => array(
                    'view' => array(
                        'formats' => array(
                            'json' => false,
                            'xml'  => true,
                        ),
                    ),
                ),
            ),
            $this->container
        );

        $yamlCollectionLoaderDefinitionName = 'fos_rest.routing.loader.yaml_collection';
        $yamlCollectionLoaderClassParameter = 'fos_rest.routing.loader.yaml_collection.class';
        $this->assertValidRestFileLoader(
            $this->container->getDefinition($yamlCollectionLoaderDefinitionName),
            $yamlCollectionLoaderClassParameter,
            $this->includeFormat,
            array(
                'xml'  => false,
                'html' => true,
            ),
            $this->defaultFormat
        );

        $xmlCollectionLoaderDefinitionName  = 'fos_rest.routing.loader.xml_collection';
        $xmlCollectionLoaderClassParameter  = 'fos_rest.routing.loader.xml_collection.class';
        $this->assertValidRestFileLoader(
            $this->container->getDefinition($xmlCollectionLoaderDefinitionName),
            $xmlCollectionLoaderClassParameter,
            $this->includeFormat,
            array(
                'xml'  => false,
                'html' => true,
            ),
            $this->defaultFormat
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testLoadBadClassThrowsException()
    {
        $this->extension->load(array(
            'fos_rest' => array(
                'exception' => array(
                    'messages' => array(
                        'UnknownException' => true,
                    ),
                ),
            ),
        ), $this->container);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Could not load class 'UnknownException' or the class does not extend from '\Exception'
     */
    public function testLoadBadMessagesClassThrowsException()
    {
        $this->extension->load(array(
            'fos_rest' => array(
                'exception' => array(
                    'codes' => array(
                        'UnknownException' => 404,
                    ),
                ),
            ),
        ), $this->container);
    }

    public function testLoadOkMessagesClass()
    {
        $this->extension->load(array(
            'fos_rest' => array(
                'exception' => array(
                    'codes' => array(
                        'Exception' => 404,
                    ),
                ),
            ),
        ), $this->container);
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
        $this->extension->load(array(
            'fos_rest' => array(
                'exception' => array(
                    'codes' => array(
                        'Exception' => $value,
                    ),
                ),
            ),
        ), $this->container);
    }

    public function getLoadBadCodeValueThrowsExceptionData()
    {
        $data = array(
            null,
            'HTTP_NOT_EXISTS',
            'some random string',
            true,
        );

        return array_map(function ($i) {
            return array($i);
        }, $data);
    }

    /**
     * Assert that loader definition described properly.
     *
     * @param Definition $loader               loader definition
     * @param string     $loaderClassParameter loader class parameter name
     */
    private function assertValidRestRouteLoader(Definition $loader, $loaderClassParameter)
    {
        $arguments = $loader->getArguments();

        $this->assertEquals('%'.$loaderClassParameter.'%', $loader->getClass());
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
     * @param Definition $loader               loader definition
     * @param string     $loaderClassParameter loader class parameter name
     * @param bool       $includeFormat        whether or not the requested view format must be included in the route path
     * @param string[]   $formats              supported view formats
     * @param string     $defaultFormat        default view format
     */
    private function assertValidRestFileLoader(
        Definition $loader,
        $loaderClassParameter,
        $includeFormat,
        array $formats,
        $defaultFormat
    ) {
        $locatorRef = new Reference('file_locator');
        $processorRef = new Reference('fos_rest.routing.loader.processor');
        $arguments  = $loader->getArguments();

        $this->assertEquals('%'.$loaderClassParameter.'%', $loader->getClass());
        $this->assertEquals(5, count($arguments));
        $this->assertEquals($locatorRef, $arguments[0]);
        $this->assertEquals($processorRef, $arguments[1]);
        $this->assertEquals(
            $includeFormat,
            $this->container->getParameter(
                strtr($arguments[2], array('%' => ''))
            )
        );
        $this->assertEquals(
            $formats,
            $this->container->getParameter(
                strtr($arguments[3], array('%' => ''))
            )
        );
        $this->assertEquals(
            $defaultFormat,
            $this->container->getParameter(
                strtr($arguments[4], array('%' => ''))
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
        $this->extension->load(array('fos_rest' => array('view' => array('jsonp_handler' => null))), $this->container);

        $this->assertTrue($this->container->has('fos_rest.view_handler'));

        $viewHandler = $this->container->getDefinition('fos_rest.view_handler');
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\DefinitionDecorator', $viewHandler);
    }

    public function testCheckExceptionWrapperHandler()
    {
        $this->extension->load(array(), $this->container);

        $this->assertTrue($this->container->has('fos_rest.view.exception_wrapper_handler'));

        $exceptionWrapperHandler = $this->container->getDefinition('fos_rest.view.exception_wrapper_handler');
        $this->assertEquals('%fos_rest.view.exception_wrapper_handler%', $exceptionWrapperHandler->getClass());
    }

    /**
     * @expectedException \LogicException
     */
    public function testExceptionThrownIfCallbackFilterIsUsed()
    {
        $this->extension->load(array('fos_rest' => array('view' => array('jsonp_handler' => array('callback_filter' => 'foo')))), $this->container);
    }
}
