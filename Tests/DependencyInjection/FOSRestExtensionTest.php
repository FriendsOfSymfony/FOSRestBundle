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

use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Definition,
    Symfony\Component\DependencyInjection\Reference;

use FOS\RestBundle\DependencyInjection\FOSRestExtension;

/**
 * FOSRestExtension test.
 *
 * @author Bulat Shakirzyanov <avalanche123>
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FOSRestExtensionTest extends \PHPUnit_Framework_TestCase
{
    private $container;
    private $extension;

    public function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->extension = new FOSRestExtension();
    }

    public function tearDown()
    {
        unset($this->container, $this->extension);
    }

    public function testDisableBodyListener()
    {
        $config = array(
            'fos_rest' => array('body_listener' => false)
        );
        $this->extension->load($config, $this->container);

        $this->assertFalse($this->container->hasDefinition('fos_rest.body_listener'));
    }

    public function testLoadBodyListenerWithDefaults()
    {
        $this->extension->load(array(), $this->container);
        $decoders = array(
            'json' => 'fos_rest.decoder.json',
            'xml' => 'fos_rest.decoder.xml'
        );

        $this->assertTrue($this->container->hasDefinition('fos_rest.body_listener'));
        $this->assertParameter($decoders, 'fos_rest.decoders');
    }

    public function testDisableFormatListener()
    {
        $config = array(
            'fos_rest' => array('format_listener' => false)
        );
        $this->extension->load($config, $this->container);

        $this->assertFalse($this->container->hasDefinition('fos_rest.format_listener'));
    }

    public function testLoadFormatListenerWithDefaults()
    {
        $this->extension->load(array(), $this->container);

        $this->assertTrue($this->container->hasDefinition('fos_rest.format_listener'));
        $this->assertParameter(array('html', '*/*'), 'fos_rest.default_priorities');
        $this->assertParameter('html', 'fos_rest.fallback_format');
    }

    public function testLoadServicesWithDefaults()
    {
        $this->extension->load(array(), $this->container);

        $this->assertAlias('fos_rest.view_handler.default', 'fos_rest.view_handler');
    }

    public function testLoadFormatsWithDefaults()
    {
        $this->extension->load(array(), $this->container);
        $formats = array(
            'json' => false,
            'xml' => false,
            'html' => true
        );

        $this->assertEquals($formats, $this->container->getParameter('fos_rest.formats'));
    }

    public function testDisableViewResponseListener()
    {
        $config = array(
            'fos_rest' => array('view' => array('view_response_listener' => false))
        );
        $this->extension->load($config, $this->container);

        $this->assertFalse($this->container->hasDefinition('fos_rest.view_response_listener'));
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
            $yamlCollectionLoaderClassParameter
        );

        $this->assertEquals($xmlCollectionLoaderClass, $this->container->getParameter($xmlCollectionLoaderClassParameter));
        $this->assertTrue($this->container->hasDefinition($xmlCollectionLoaderDefinitionName));
        $this->assertValidRestFileLoader(
            $this->container->getDefinition($xmlCollectionLoaderDefinitionName),
            $xmlCollectionLoaderClassParameter
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testLoadBadClassThrowsException()
    {
        $this->extension->load(array('fos_rest' => array('exception' => array('messages'=> array('UnknownException' => true)))), $this->container);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testLoadBadMessagesClassThrowsException()
    {
        $this->extension->load(array('fos_rest' => array('exception' => array('codes'=> array('UnknownException' => 404)))), $this->container);
    }

    public function testLoadOkMessagesClass()
    {
        $this->extension->load(array('fos_rest' => array('exception' => array('codes'=> array('\Exception' => 404)))), $this->container);
        $this->assertFalse($this->container->hasDefinition('fos_rest.exception.codes'));
    }

    /**
     * Assert that loader definition described properly.
     *
     * @param   Definition  $loader                 loader definition
     * @param   string      $loaderClassParameter   loader class parameter name
     */
    private function assertValidRestRouteLoader(Definition $loader, $loaderClassParameter)
    {
        $arguments = $loader->getArguments();

        $this->assertEquals('%' . $loaderClassParameter . '%', $loader->getClass());
        $this->assertEquals(4, count($arguments));
        $this->assertEquals('service_container', (string) $arguments[0]);
        $this->assertEquals('controller_name_converter', (string) $arguments[1]);
        $this->assertEquals('fos_rest.routing.loader.reader.controller', (string) $arguments[2]);
        $this->assertEquals('%fos_rest.routing.loader.default_format%', (string) $arguments[3]);
        $this->assertArrayHasKey('routing.loader', $loader->getTags());
    }

    /**
     * Assert that loader definition described properly.
     *
     * @param   Definition  $loader                 loader definition
     * @param   string      $loaderClassParameter   loader class parameter name
     */
    private function assertValidRestFileLoader(Definition $loader, $loaderClassParameter)
    {
        $locatorRef = new Reference('file_locator');
        $processorRef = new Reference('fos_rest.routing.loader.processor');
        $arguments  = $loader->getArguments();

        $this->assertEquals('%' . $loaderClassParameter . '%', $loader->getClass());
        $this->assertEquals(2, count($arguments));
        $this->assertEquals($locatorRef, $arguments[0]);
        $this->assertEquals($processorRef, $arguments[1]);
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
}
