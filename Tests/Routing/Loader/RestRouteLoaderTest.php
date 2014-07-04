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

use FOS\RestBundle\Routing\RestRouteCollection;
use Symfony\Component\Routing\RouteCollection;

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
        $this->assertEquals(26, count($collection->all()));

        foreach ($etalonRoutes as $name => $params) {
            $route = $collection->get($name);

            $this->assertNotNull($route, sprintf('route for %s does not exist', $name));
            $this->assertEquals($params['pattern'], $route->getPattern(), 'Pattern does not match for route: '.$name);
            $this->assertEquals($params['method'], $route->getRequirement('_method'), 'Method does not match for route: '.$name);
            $this->assertContains($params['controller'], $route->getDefault('_controller'), 'Controller does not match for route: '.$name);
        }
    }

    /**
     * Test that ResourceController RESTful class gets parsed correctly.
     */
    public function testResourceFixture()
    {
        $collection     = $this->loadFromControllerFixture('ArticleController');
        $etalonRoutes   = $this->loadEtalonRoutesInfo('resource_controller.yml');

        $this->assertTrue($collection instanceof RestRouteCollection);
        $this->assertEquals(24, count($collection->all()));

        foreach ($etalonRoutes as $name => $params) {
            $route = $collection->get($name);

            $this->assertNotNull($route, sprintf('route for %s does not exist', $name));
            $this->assertEquals($params['pattern'], $route->getPattern(), 'Pattern does not match for route: '.$name);
            $this->assertEquals($params['method'], $route->getRequirement('_method'), 'Method does not match for route: '.$name);
            $this->assertContains($params['controller'], $route->getDefault('_controller'), 'Controller does not match for route: '.$name);
        }
    }

    /**
     * Test that custom actions (new/edit/remove) are dumped earlier.
     */
    public function testCustomActionRoutesOrder()
    {
        // without prefix

        $collection = $this->loadFromControllerFixture('OrdersController');
        $pos = array_flip(array_keys($collection->all()));

        $this->assertLessThan($pos['get_foos'], $pos['new_foos']);
        $this->assertLessThan($pos['get_bars'], $pos['new_bars']);

        // with prefix

        $collection = $this->loadFromControllerFixture('OrdersController', 'prefix_');
        $pos = array_flip(array_keys($collection->all()));

        $this->assertLessThan($pos['prefix_get_foos'], $pos['prefix_new_foos']);
        $this->assertLessThan($pos['prefix_get_bars'], $pos['prefix_new_bars']);
    }

    /**
     * Test that annotated UsersController RESTful class gets parsed correctly.
     */
    public function testAnnotatedUsersFixture()
    {
        $collection     = $this->loadFromControllerFixture('AnnotatedUsersController');
        $etalonRoutes   = $this->loadEtalonRoutesInfo('annotated_users_controller.yml');

        $this->assertTrue($collection instanceof RestRouteCollection);
        $this->assertEquals(21, count($collection->all()));

        foreach ($etalonRoutes as $name => $params) {
            $route = $collection->get($name);

            $this->assertNotNull($route, "no route found for '$name'");
            $this->assertEquals($params['pattern'], $route->getPattern(), 'pattern failed to match for '.$name);
            $this->assertEquals($params['requirements'], $route->getRequirements(), 'requirements failed to match for '.$name);
            $this->assertContains($params['controller'], $route->getDefault('_controller'), 'controller failed to match for '.$name);
            if (isset($params['condition'])) {
                 $this->assertEquals($params['condition'], $route->getCondition(), 'condition failed to match for '.$name);
            }

        }
    }
    /**
     * Test that annotated UsersController RESTful class gets parsed correctly with condition option (expression-language).
     */
    public function testAnnotatedConditionalUsersFixture()
    {

        if (!method_exists('Symfony\Component\Routing\Annotation\Route','setCondition')) {
            $this->markTestSkipped('The "Routing" component have a version <2.4');
            return;
        }

        $collection     = $this->loadFromControllerFixture('AnnotatedConditionalUsersController');
        $etalonRoutes   = $this->loadEtalonRoutesInfo('annotated_conditional_controller.yml');

        $this->assertTrue($collection instanceof RestRouteCollection);
        $this->assertEquals(22, count($collection->all()));

        foreach ($etalonRoutes as $name => $params) {
            $route = $collection->get($name);

            $this->assertNotNull($route, "no route found for '$name'");
            $this->assertEquals($params['pattern'], $route->getPattern(), 'pattern failed to match for '.$name);
            $this->assertEquals($params['requirements'], $route->getRequirements(), 'requirements failed to match for '.$name);
            $this->assertContains($params['controller'], $route->getDefault('_controller'), 'controller failed to match for '.$name);
            if (isset($params['condition'])) {
                $this->assertEquals($params['condition'], $route->getCondition(), 'condition failed to match for '.$name);
            }

        }
    }

    /**
     * Test that a custom format annotation is not overwritten
     */
    public function testCustomFormatRequirementIsKept()
    {
        $collection = $this->loadFromControllerFixture(
            'AnnotatedUsersController',
            null,
            array('json' => true, 'xml' => true, 'html' => true)
        );
        $routeCustom            = $collection->get('custom_user');
        $routeWithRequirements  = $collection->get('get_user');

        $this->assertEquals('custom', $routeCustom->getRequirement('_format'));
        $this->assertEquals('json|xml|html', $routeWithRequirements->getRequirement('_format'));
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
     * Test that custom actions (new/edit/remove) are dumped earlier,
     * and that developer routes order is kept
     *
     * @see https://github.com/FriendsOfSymfony/RestBundle/issues/379
     */
    public function testCustomActionRoutesInDeveloperOrder()
    {
        // without prefix

        $collection = $this->loadFromControllerFixture('OrdersController');
        $pos = array_flip(array_keys($collection->all()));

        $this->assertLessThan($pos['get_bars'], $pos['get_bars_custom']);

        // with prefix

        $collection = $this->loadFromControllerFixture('OrdersController', 'prefix_');
        $pos = array_flip(array_keys($collection->all()));

        $this->assertLessThan($pos['prefix_get_bars'], $pos['prefix_get_bars_custom']);
    }

    /**
     * Test if the routes are also working with uninflected words
     *
     * @see https://github.com/FriendsOfSymfony/FOSRestBundle/pull/761
     */
    public function testMediaFixture()
    {
        $expectedMethod = 'GET';
        $collection = $this->loadFromControllerFixture('MediaController');

        $this->assertCount(2, $collection->all());
        $this->assertEquals($expectedMethod, $collection->get('get_media')->getRequirement('_method'));
        $this->assertEquals($expectedMethod, $collection->get('cget_media')->getRequirement('_method'));
    }

    /**
     * Test if the routes are also working with uninflected words
     *
     * @see https://github.com/FriendsOfSymfony/FOSRestBundle/pull/761
     */
    public function testInformationFixture()
    {
        $collection = $this->loadFromControllerFixture('InformationController');

        $this->assertCount(2, $collection->all());

        $getRoute = $collection->get('get_information');
        $cgetRoute = $collection->get('cget_information');

        $this->assertEquals($getRoute, $cgetRoute);
        $this->assertNotSame($getRoute, $cgetRoute);
    }

    /**
     * @see https://github.com/FriendsOfSymfony/FOSRestBundle/issues/770
     * @see https://github.com/FriendsOfSymfony/FOSRestBundle/pull/792
     */
    public function testNamePrefixIsPrependingCorrectly()
    {
        $collection = $this->loadFromControllerFixture('InformationController', 'prefix_');

        $this->assertNotNull($collection->get('prefix_get_information'));
        $this->assertNotNull($collection->get('prefix_cget_information'));
    }

    /**
     * Load routes collection from fixture class under Tests\Fixtures directory.
     *
     * @param string $fixtureName name of the class fixture
     * @param string $namePrefix  route name prefix
     * @param array  $formats     resource formats available
     *
     * @return RouteCollection
     */
    protected function loadFromControllerFixture($fixtureName, $namePrefix = null, array $formats = array())
    {
        $loader = $this->getControllerLoader($formats);
        $loader->getControllerReader()->getActionReader()->setNamePrefix($namePrefix);

        return $loader->load('FOS\RestBundle\Tests\Fixtures\Controller\\'. $fixtureName, 'rest');
    }
}
