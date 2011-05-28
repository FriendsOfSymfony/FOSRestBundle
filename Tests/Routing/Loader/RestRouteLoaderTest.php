<?php

namespace FOS\RestBundle\Tests\Routing\Loader;

use FOS\RestBundle\Routing\Loader\RestRouteLoader,
    FOS\RestBundle\Routing\RestRouteCollection;

/*
 * This file is part of the FOSRestBundle
 *
 * (c) Lukas Kahwe Smith <smith@pooteeweet.org>
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 * (c) Bulat Shakirzyanov <mallluhuct@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * RestRouteLoader test.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class RestRouteLoaderTest extends LoaderTest
{
    /**
     * Test that UsersController RESTful class gets parsed correctly.
     */
    public function testUsersFixture()
    {
        $collection     = $this->loadFromControllerFixture('UsersController');
        $etalonRoutes   = $this->loadEtalonRoutesInfo('users_controller.yml');

        $this->assertTrue($collection instanceof RestRouteCollection);
        $this->assertEquals(13, count($collection->all()));

        foreach ($etalonRoutes as $name => $params) {
            $route = $collection->get($name);

            $this->assertNotNull($route, sprintf('route %s exists', $name));
            $this->assertEquals($params['pattern'], $route->getPattern());
            $this->assertEquals($params['method'], $route->getRequirement('_method'));
            $this->assertContains($params['controller'], $route->getDefault('_controller'));
        }
    }

    /**
     * Test that annotated UsersController RESTful class gets parsed correctly.
     */
    public function testAnnotatedUsersFixture()
    {
        $collection     = $this->loadFromControllerFixture('AnnotatedUsersController');
        $etalonRoutes   = $this->loadEtalonRoutesInfo('annotated_users_controller.yml');

        $this->assertTrue($collection instanceof RestRouteCollection);
        $this->assertEquals(10, count($collection->all()));

        foreach ($etalonRoutes as $name => $params) {
            $route = $collection->get($name);

            $this->assertNotNull($route);
            $this->assertEquals($params['pattern'], $route->getPattern());
            $this->assertEquals($params['requirements'], $route->getRequirements());
            $this->assertContains($params['controller'], $route->getDefault('_controller'));
        }
    }

    /**
     * @see https://github.com/FriendsOfSymfony/RestBundle/issues/37
     */
    public function testPrefixIsResetForEachController()
    {
        // we can't use the getControllerLoader method because we need to verify that the prefix
        // is reset when using the same ControllerLoader for both Controllers.
        $loader = $this->getControllerLoader();

        // get the pattern for the prefixed controller, and verify it is prefixed
        $collection = $loader->load('FOS\RestBundle\Tests\Fixtures\Controller\AnnotatedPrefixedController', 'rest');
        $prefixedRoute = $collection->get('get_something');
        $this->assertTrue(substr($prefixedRoute->getPattern(), 0, 9) == '/aprefix/');

        // get the pattern for the non-prefixed controller, and verify it's not prefixed
        $collection2 = $loader->load('FOS\RestBundle\Tests\Fixtures\Controller\UsersController', 'rest');
        $nonPrefixedRoute = $collection2->get('get_users');
        $this->assertFalse(substr($nonPrefixedRoute->getPattern(), 0, 9) == '/aprefix/');
    }

    /**
     * Load routes collection from fixture class under Tests\Fixtures directory.
     *
     * @param   string  $fixtureName    name of the class fixture
     */
    protected function loadFromControllerFixture($fixtureName)
    {
        $loader = $this->getControllerLoader();

        return $loader->load('FOS\RestBundle\Tests\Fixtures\Controller\\'. $fixtureName, 'rest');
    }
}
