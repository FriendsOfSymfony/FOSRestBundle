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
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollection;

/**
 * RestYamlCollectionLoader test.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class RestYamlCollectionLoaderTest extends LoaderTest
{
    /**
     * Test that YAML file is empty.
     */
    public function testLoadDoesNothingIfEmpty()
    {
        $collection = $this->loadFromYamlCollectionFixture('empty.yml');
        $this->assertEquals([], $collection->all());
    }

    /**
     * Test that invalid YAML format.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /The file "*.+\/bad_format\.yml" does not contain valid YAML\./
     */
    public function testLoadThrowsExceptionWithInvalidYaml()
    {
        $this->loadFromYamlCollectionFixture('bad_format.yml');
    }

    /**
     * Test that YAML value not an array.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /The file "*.+\/nonvalid.yml" must contain a Yaml mapping \(an array\)\./
     */
    public function testLoadThrowsExceptionWithValueNotArray()
    {
        $this->loadFromYamlCollectionFixture('nonvalid.yml');
    }

    /**
     * Test that route parent not found.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Cannot find parent resource with name
     */
    public function testLoadThrowsExceptionWithInvalidRouteParent()
    {
        $this->loadFromYamlCollectionFixture('invalid_route_parent.yml');
    }

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
        $this->assertCount(count($names), array_unique($names));
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
     * @see https://github.com/FriendsOfSymfony/FOSRestBundle/issues/604#issuecomment-40284026
     */
    public function testControllerAsServiceWithClassName()
    {
        $controller = new UsersController();

        // We register the controller in the fake container by its class name
        $this->container = new Container();
        $this->container->set(get_class($controller), $controller);

        $collection = $this->loadFromYamlCollectionFixture('users_collection.yml');

        $route = $collection->get('get_users');

        // We check that it's "controller:method" if sf < 4.1 (controller as service) and not "controller::method"
        $this->assertEquals(
            UsersController::class.(Kernel::VERSION_ID >= 40100 ? '::' : ':').'getUsersAction',
            $route->getDefault('_controller')
        );

        $this->container = null;
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

    /**
     * @group legacy
     */
    public function testRoutesWithPattern()
    {
        $collection = $this->loadFromYamlCollectionFixture('routes_with_pattern.yml');
        $route = $collection->get('get_users');

        $this->assertEquals('/users.{_format}', $route->getPath());
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

    public function testHostnameFixture()
    {
        $collection = $this->loadFromYamlCollectionFixture('routes.yml');
        $route = $collection->get('get_users');

        $this->assertEquals('rest.local', $route->getHost());
    }
}
