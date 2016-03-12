<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Routing\Loader;

use FOS\RestBundle\Routing\Loader\RestRouteProcessor;
use FOS\RestBundle\Routing\Loader\RestYamlCollectionLoader;
use FOS\RestBundle\Tests\Fixtures\Controller\UsersController;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Routing\RouteCollection;

/**
 * RestYamlCollectionLoader test.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class RestYamlCollectionLoaderTest extends LoaderTest
{
    /**
     * Test that YAML collection gets parsed correctly.
     */
    public function testUsersFixture()
    {
        $collection = $this->loadFromYamlCollectionFixture('users_collection.yml');
        $etalonRoutes = $this->loadEtalonRoutesInfo('users_collection.yml');

        foreach ($etalonRoutes as $name => $params) {
            $route = $collection->get($name);
            $methods = $route->getMethods();

            $this->assertNotNull($route, $name);
            $this->assertEquals($params['path'], $route->getPath(), $name);
            $this->assertEquals($params['methods'][0], $methods[0], $name);
            $this->assertContains($params['controller'], $route->getDefault('_controller'), $name);
        }
    }

    /**
     * Test that YAML collection with custom prefixes gets parsed correctly.
     */
    public function testPrefixedUsersFixture()
    {
        $collection = $this->loadFromYamlCollectionFixture('prefixed_users_collection.yml');
        $etalonRoutes = $this->loadEtalonRoutesInfo('prefixed_users_collection.yml');

        foreach ($etalonRoutes as $name => $params) {
            $route = $collection->get($name);
            $methods = $route->getMethods();

            $this->assertNotNull($route, $name);
            $this->assertEquals($params['path'], $route->getPath(), $name);
            $this->assertEquals($params['methods'][0], $methods[0], $name);
            $this->assertContains($params['controller'], $route->getDefault('_controller'), $name);
        }
    }

    /**
     * Test that YAML collection with named prefixes gets parsed correctly.
     */
    public function testNamedPrefixedReportsFixture()
    {
        $collection = $this->loadFromYamlCollectionFixture('named_prefixed_reports_collection.yml');
        $etalonRoutes = $this->loadEtalonRoutesInfo('named_prefixed_reports_collection.yml');

        foreach ($etalonRoutes as $name => $params) {
            $route = $collection->get($name);
            $methods = $route->getMethods();

            $this->assertNotNull($route, $name);
            $this->assertEquals($params['path'], $route->getPath(), $name);
            $this->assertEquals($params['methods'][0], $methods[0], $name);
            $this->assertContains($params['controller'], $route->getDefault('_controller'), $name);
        }
    }

    /**
     * Test that collection with named prefixes has no duplicates.
     */
    public function testNamedPrefixedReportsFixtureHasNoDuplicates()
    {
        $names = [];
        $collection = $this->loadFromYamlCollectionFixture('named_prefixed_reports_collection.yml');
        foreach ($collection as $route) {
            $names[] = $route->getPath();
        }
        $this->assertEquals(count($names), count(array_unique($names)));
    }

    public function testForwardOptionsRequirementsAndDefaults()
    {
        $collection = $this->loadFromYamlCollectionFixture('routes_with_options_requirements_and_defaults.yml');

        foreach ($collection as $route) {
            $this->assertTrue($route->getOption('expose'));
            $this->assertEquals('[a-z]+', $route->getRequirement('slug'));
            $this->assertEquals('home', $route->getDefault('slug'));
        }
    }

    public function testManualRoutes()
    {
        $collection = $this->loadFromYamlCollectionFixture('routes.yml');
        $route = $collection->get('get_users');

        $this->assertEquals('/users.{_format}', $route->getPath());
        $this->assertEquals('json|xml|html', $route->getRequirement('_format'));
        $this->assertEquals('FOSRestBundle:UsersController:getUsers', $route->getDefault('_controller'));
    }

    public function testManualRoutesWithoutIncludeFormat()
    {
        $collection = $this->loadFromYamlCollectionFixture('routes.yml', false);
        $route = $collection->get('get_users');

        $this->assertEquals('/users', $route->getPath());
    }

    public function testManualRoutesWithFormats()
    {
        $collection = $this->loadFromYamlCollectionFixture(
            'routes.yml',
            true,
            [
                'json' => false,
            ]
        );
        $route = $collection->get('get_users');

        $this->assertEquals('json', $route->getRequirement('_format'));
    }

    public function testManualRoutesWithDefaultFormat()
    {
        $collection = $this->loadFromYamlCollectionFixture(
            'routes.yml',
            true,
            [
                'json' => false,
                'xml' => false,
                'html' => true,
            ],
            'xml'
        );
        $route = $collection->get('get_users');

        $this->assertEquals('xml', $route->getDefault('_format'));
    }

    /**
     * Tests that we can use "controller as service" even if the controller is registered in the
     * container by its class name.
     *
     * @link https://github.com/FriendsOfSymfony/FOSRestBundle/issues/604#issuecomment-40284026
     */
    public function testControllerAsServiceWithClassName()
    {
        $controller = new UsersController();

        // We register the controller in the fake container by its class name
        $this->containerMock = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['has', 'get', 'enterScope', 'leaveScope'])
            ->getMock();
        $this->containerMock->expects($this->any())
            ->method('has')
            ->will($this->returnCallback(function ($serviceId) use ($controller) {
                return $serviceId === get_class($controller);
            }));
        $this->containerMock->expects($this->once())
            ->method('get')
            ->with(get_class($controller))
            ->will($this->returnValue($controller));

        $collection = $this->loadFromYamlCollectionFixture('users_collection.yml');

        $route = $collection->get('get_users');

        // We check that it's "controller:method" (controller as service) and not "controller::method"
        $this->assertEquals(
            'FOS\RestBundle\Tests\Fixtures\Controller\UsersController:getUsersAction',
            $route->getDefault('_controller')
        );
    }

    /**
     * Load routes collection from YAML fixture routes under Tests\Fixtures directory.
     *
     * @param string   $fixtureName   name of the class fixture
     * @param bool     $includeFormat whether or not the requested view format must be included in the route path
     * @param string[] $formats       supported view formats
     * @param string   $defaultFormat default view format
     *
     * @return RouteCollection
     */
    protected function loadFromYamlCollectionFixture(
        $fixtureName,
        $includeFormat = true,
        array $formats = [
            'json' => false,
            'xml' => false,
            'html' => true,
        ],
        $defaultFormat = null
    ) {
        $collectionLoader = new RestYamlCollectionLoader(
            new FileLocator([__DIR__.'/../../Fixtures/Routes']),
            new RestRouteProcessor(),
            $includeFormat,
            $formats,
            $defaultFormat
        );
        $controllerLoader = $this->getControllerLoader();

        // LoaderResolver sets the resolvers on the loaders passed to it
        new LoaderResolver([$collectionLoader, $controllerLoader]);

        return $collectionLoader->load($fixtureName, 'rest');
    }

    /**
     * Test that YAML collection with named prefixes gets parsed correctly with inheritance.
     */
    public function testNamedPrefixedBaseReportsFixture()
    {
        $collection = $this->loadFromYamlCollectionFixture('base_named_prefixed_reports_collection.yml');
        $etalonRoutes = $this->loadEtalonRoutesInfo('base_named_prefixed_reports_collection.yml');

        foreach ($etalonRoutes as $name => $params) {
            $route = $collection->get($name);
            $methods = $route->getMethods();

            $this->assertNotNull($route, $name);
            $this->assertEquals($params['path'], $route->getPath(), $name);
            $this->assertEquals($params['method'], $methods[0], $name);
            $this->assertContains($params['controller'], $route->getDefault('_controller'), $name);
        }

        $name = 'api_get_billing_payments';
        $this->assertArrayNotHasKey($name, $etalonRoutes);
    }
}
