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

use FOS\RestBundle\Routing\Loader\RestRouteLoader,
    FOS\RestBundle\Routing\RestRouteCollection;

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
        $this->assertEquals(19, count($collection->all()));

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
        $this->assertEquals(12, count($collection->all()));

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
        $this->assertEquals('/aprefix/', substr($prefixedRoute->getPattern(), 0, 9));

        // get the pattern for the non-prefixed controller, and verify it's not prefixed
        $collection2 = $loader->load('FOS\RestBundle\Tests\Fixtures\Controller\UsersController', 'rest');
        $nonPrefixedRoute = $collection2->get('get_users');
        $this->assertNotEquals('/aprefix/', substr($nonPrefixedRoute->getPattern(), 0, 9));
    }

    /**
     * Test that conventional actions exist and are registered as GET methods
     * 
     * @see https://github.com/FriendsOfSymfony/RestBundle/issues/67
     */
    public function testConventionalActions()
    {
        $expectedMethod = 'GET';
        $collection = $this->loadFromControllerFixture('UsersController');
        $subcollection = $this->loadFromControllerFixture('UserTopicsController');
        $subsubcollection = $this->loadFromControllerFixture('UserTopicCommentsController');

        // resource actions
        $this->assertEquals($expectedMethod, $collection->get('new_users')->getRequirement('_method'));
        $this->assertEquals($expectedMethod, $collection->get('edit_user')->getRequirement('_method'));
        $this->assertEquals($expectedMethod, $collection->get('remove_user')->getRequirement('_method'));

        // subresource actions
        $this->assertEquals($expectedMethod, $collection->get('new_user_comments')->getRequirement('_method'));
        $this->assertEquals($expectedMethod, $collection->get('edit_user_comment')->getRequirement('_method'));
        $this->assertEquals($expectedMethod, $collection->get('remove_user_comment')->getRequirement('_method'));

        // resource collection actions
        $this->assertEquals($expectedMethod, $subcollection->get('new_topics')->getRequirement('_method'));
        $this->assertEquals($expectedMethod, $subcollection->get('edit_topic')->getRequirement('_method'));
        $this->assertEquals($expectedMethod, $subcollection->get('remove_topic')->getRequirement('_method'));

        // resource collection's resource collection actions
        $this->assertEquals($expectedMethod, $subsubcollection->get('new_comments')->getRequirement('_method'));
        $this->assertEquals($expectedMethod, $subsubcollection->get('edit_comment')->getRequirement('_method'));
        $this->assertEquals($expectedMethod, $subsubcollection->get('remove_comment')->getRequirement('_method'));
    }

    /**
     * Load routes collection from fixture class under Tests\Fixtures directory.
     *
     * @param   string  $fixtureName    name of the class fixture
     * @return  Symfony\Component\Routing\RouteCollection
     */
    protected function loadFromControllerFixture($fixtureName)
    {
        $loader = $this->getControllerLoader();

        return $loader->load('FOS\RestBundle\Tests\Fixtures\Controller\\'. $fixtureName, 'rest');
    }
}
