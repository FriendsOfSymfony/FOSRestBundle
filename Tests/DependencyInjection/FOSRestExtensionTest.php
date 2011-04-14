<?php

namespace FOS\RestBundle\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Definition,
    Symfony\Component\DependencyInjection\Reference;

use FOS\RestBundle\DependencyInjection\FOSRestExtension;

/*
 * This file is part of the FOS/RestBundle
 *
 * (c) Lukas Kahwe Smith <smith@pooteeweet.org>
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 * (c) Bulat Shakirzyanov <mallluhuct@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

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

        $controllerAnnotationsNS          = 'FOS\RestBundle\Controller\Annotations\\';
        $controllerAnnotationsNSParameter = 'fos_rest.routing.loader.controller.annotations_namespace';

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

        $this->assertEquals($controllerAnnotationsNS, $this->container->getParameter($controllerAnnotationsNSParameter));
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
        $this->assertEquals(3, count($arguments));
        $this->assertEquals('service_container', (string) $arguments[0]);
        $this->assertEquals('controller_name_converter', (string) $arguments[1]);
        $this->assertValidAnnotationReader($this->container->getDefinition((string) $arguments[2]));
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
        $locatorRef = new Reference('routing.file_locator');
        $arguments  = $loader->getArguments();

        $this->assertEquals('%' . $loaderClassParameter . '%', $loader->getClass());
        $this->assertEquals(1, count($arguments));
        $this->assertEquals($locatorRef, $arguments[0]);
        $this->assertArrayHasKey('routing.loader', $loader->getTags());
    }

    /**
     * Assert that definition of AnnotationReader is valid
     *
     * @param   Definition   $reader                  reader definition
     */
    private function assertValidAnnotationReader(Definition $reader)
    {
        $annotationReaderClass  = 'Doctrine\Common\Annotations\AnnotationReader';
        $annotationsNSParameter = 'fos_rest.routing.loader.controller.annotations_namespace';
        $annotationsAlias       = 'rest';
        $methodName             = 'setAnnotationNamespaceAlias';

        $readerCalls = $reader->getMethodCalls();

        $this->assertEquals($annotationReaderClass, $reader->getClass());
        $this->assertEquals(1, count($readerCalls));
        $this->assertEquals(array($methodName, array('%' . $annotationsNSParameter . '%', $annotationsAlias)), $readerCalls[0]);
    }
}
