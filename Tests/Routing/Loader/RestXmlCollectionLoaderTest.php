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
use FOS\RestBundle\Routing\Loader\RestXmlCollectionLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Routing\RouteCollection;

/**
 * RestXmlCollectionLoader test.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class RestXmlCollectionLoaderTest extends LoaderTest
{
    /**
     * Test that XML collection gets parsed correctly.
     */
    public function testUsersFixture()
    {
        $collection = $this->loadFromXmlCollectionFixture('users_collection.xml');
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
     * Test that XML collection with custom prefixes gets parsed correctly.
     */
    public function testPrefixedUsersFixture()
    {
        $collection = $this->loadFromXmlCollectionFixture('prefixed_users_collection.xml');
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

    public function testManualRoutes()
    {
        $collection = $this->loadFromXmlCollectionFixture('routes.xml');
        $route = $collection->get('get_users');

        $this->assertEquals('/users.{_format}', $route->getPath());
        $this->assertEquals('json|xml|html', $route->getRequirement('_format'));
        $this->assertEquals('FOSRestBundle:UsersController:getUsers', $route->getDefault('_controller'));
    }

    public function testManualRoutesWithoutIncludeFormat()
    {
        $collection = $this->loadFromXmlCollectionFixture('routes.xml', false);
        $route = $collection->get('get_users');

        $this->assertEquals('/users', $route->getPath());
    }

    public function testManualRoutesWithFormats()
    {
        $collection = $this->loadFromXmlCollectionFixture(
            'routes.xml',
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
        $collection = $this->loadFromXmlCollectionFixture(
            'routes.xml',
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

    public function testForwardOptionsRequirementsAndDefaults()
    {
        $collection = $this->loadFromXmlCollectionFixture('routes_with_options_requirements_and_defaults.xml');

        foreach ($collection as $route) {
            $this->assertTrue('true' === $route->getOption('expose'));
            $this->assertEquals('[a-z]+', $route->getRequirement('slug'));
            $this->assertEquals('home', $route->getDefault('slug'));
        }
    }

    /**
     * Load routes collection from XML fixture routes under Tests\Fixtures directory.
     *
     * @param string   $fixtureName   name of the class fixture
     * @param bool     $includeFormat whether or not the requested view format must be included in the route path
     * @param string[] $formats       supported view formats
     * @param string   $defaultFormat default view format
     *
     * @return RouteCollection
     */
    protected function loadFromXmlCollectionFixture(
        $fixtureName,
        $includeFormat = true,
        array $formats = [
            'json' => false,
            'xml' => false,
            'html' => true,
        ],
        $defaultFormat = null
    ) {
        $collectionLoader = new RestXmlCollectionLoader(
            new FileLocator([__DIR__.'/../../Fixtures/Routes']),
            new RestRouteProcessor(),
            $includeFormat,
            $formats,
            $defaultFormat
        );
        $controllerLoader = $this->getControllerLoader();

        new LoaderResolver([$collectionLoader, $controllerLoader]);

        return $collectionLoader->load($fixtureName, 'rest');
    }
}
