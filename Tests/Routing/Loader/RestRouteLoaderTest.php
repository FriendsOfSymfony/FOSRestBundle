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
        $collection = $this->loadFromControllerFixture('UsersController');
        $etalonRoutes = $this->loadEtalonRoutesInfo('users_controller.yml');

        $this->assertInstanceOf(RestRouteCollection::class, $collection);
        $this->assertCount(32, $collection->all());

        foreach ($etalonRoutes as $name => $params) {
            $route = $collection->get($name);
            $methods = $route->getMethods();

            $this->assertNotNull($route, sprintf('route for %s does not exist', $name));
            $this->assertEquals($params['path'], $route->getPath(), 'Path does not match for route: '.$name);
            $this->assertEquals($params['methods'][0], $methods[0], 'Method does not match for route: '.$name);
            $this->assertContains($params['controller'], $route->getDefault('_controller'), 'Controller does not match for route: '.$name);
        }
    }

    /**
     * Test that ResourceController RESTful class gets parsed correctly.
     */
    public function testResourceFixture()
    {
        $collection = $this->loadFromControllerFixture('ArticleController');
        $etalonRoutes = $this->loadEtalonRoutesInfo('resource_controller.yml');

        $this->assertInstanceOf(RestRouteCollection::class, $collection);
        $this->assertCount(24, $collection->all());

        foreach ($etalonRoutes as $name => $params) {
            $route = $collection->get($name);
            $methods = $route->getMethods();

            $this->assertNotNull($route, sprintf('route for %s does not exist', $name));
            $this->assertEquals($params['path'], $route->getPath(), 'Path does not match for route: '.$name);
            $this->assertEquals($params['methods'][0], $methods[0], 'Method does not match for route: '.$name);
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
        $collection = $this->loadFromControllerFixture('AnnotatedUsersController');
        $etalonRoutes = $this->loadEtalonRoutesInfo('annotated_users_controller.yml');

        $this->assertInstanceOf(RestRouteCollection::class, $collection);
        $this->assertCount(33, $collection->all());

        foreach ($etalonRoutes as $name => $params) {
            $route = $collection->get($name);

            $this->assertNotNull($route, "no route found for '$name'");
            $this->assertEquals($params['path'], $route->getPath(), 'path failed to match for '.$name);

            $params['requirements'] = isset($params['requirements']) ? $params['requirements'] : array();
            $requirements = $route->getRequirements();
            unset($requirements['_method']);
            $this->assertEquals($params['requirements'], $requirements, 'requirements failed to match for '.$name);

            $this->assertContains($params['controller'], $route->getDefault('_controller'), 'controller failed to match for '.$name);
            if (isset($params['condition'])) {
                $this->assertEquals($params['condition'], $route->getCondition(), 'condition failed to match for '.$name);
            }

            if (isset($params['options'])) {
                foreach ($params['options'] as $option => $value) {
                    $this->assertEquals($value, $route->getOption($option));
                }
            }
        }
    }

    public function testDisabledPluralization()
    {
        $collection = $this->loadFromControllerFixture('AnnotatedNonPluralizedArticleController');

        $this->assertEquals('/article.{_format}', $collection->get('cget_article')->getPath());

        $this->assertEquals('/article/{slug}.{_format}', $collection->get('get_article')->getPath());

        $this->assertEquals('/article/{slug}/comment.{_format}', $collection->get('cget_article_comment')->getPath());

        $this->assertEquals('/article/{slug}/comment/{comment}.{_format}', $collection->get('get_article_comment')->getPath());
    }

    /**
     * Test that annotated UsersController RESTful class gets parsed correctly with condition option (expression-language).
     */
    public function testAnnotatedConditionalUsersFixture()
    {
        $collection = $this->loadFromControllerFixture('AnnotatedConditionalUsersController');
        $etalonRoutes = $this->loadEtalonRoutesInfo('annotated_conditional_controller.yml');

        $this->assertInstanceOf(RestRouteCollection::class, $collection);
        $this->assertCount(22, $collection->all());

        foreach ($etalonRoutes as $name => $params) {
            $route = $collection->get($name);

            $this->assertNotNull($route, "no route found for '$name'");
            $this->assertEquals($params['path'], $route->getPath(), 'path failed to match for '.$name);

            $params['requirements'] = isset($params['requirements']) ? $params['requirements'] : array();
            $requirements = $route->getRequirements();
            unset($requirements['_method']);
            $this->assertEquals($params['requirements'], $requirements, 'requirements failed to match for '.$name);

            $this->assertContains($params['controller'], $route->getDefault('_controller'), 'controller failed to match for '.$name);
            if (isset($params['condition'])) {
                $this->assertEquals($params['condition'], $route->getCondition(), 'condition failed to match for '.$name);
            }
        }
    }

    /**
     * Test that annotated UsersController RESTful class gets parsed correctly with condition option (expression-language).
     */
    public function testAnnotatedVersionUserFixture()
    {
        $collection = $this->loadFromControllerFixture('AnnotatedVersionUserController');
        $etalonRoutes = $this->loadEtalonRoutesInfo('annotated_version_controller.yml');

        $this->assertInstanceOf(RestRouteCollection::class, $collection);
        $this->assertCount(3, $collection->all());

        foreach ($etalonRoutes as $name => $params) {
            $route = $collection->get($name);

            $this->assertNotNull($route, "no route found for '$name'");
            $this->assertEquals($params['path'], $route->getPath(), 'path failed to match for '.$name);

            $params['requirements'] = isset($params['requirements']) ? $params['requirements'] : array();
            $requirements = $route->getRequirements();
            unset($requirements['_method']);
            $this->assertEquals($params['requirements'], $requirements, 'requirements failed to match for '.$name);

            $this->assertContains($params['controller'], $route->getDefault('_controller'), 'controller failed to match for '.$name);
            if (isset($params['condition'])) {
                $this->assertEquals($params['condition'], $route->getCondition(), 'condition failed to match for '.$name);
            }
        }
    }

    /**
     * Test that a custom format annotation is not overwritten.
     */
    public function testCustomFormatRequirementIsKept()
    {
        $collection = $this->loadFromControllerFixture(
            'AnnotatedUsersController',
            null,
            ['json' => true, 'xml' => true, 'html' => true]
        );
        $routeCustom = $collection->get('custom_user');
        $routeWithRequirements = $collection->get('get_user');

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

        // get the path for the prefixed controller, and verify it is prefixed
        $collection = $loader->load('FOS\RestBundle\Tests\Fixtures\Controller\AnnotatedPrefixedController', 'rest');
        $prefixedRoute = $collection->get('get_something');
        $this->assertEquals('/aprefix/', substr($prefixedRoute->getPath(), 0, 9));

        // get the path for the non-prefixed controller, and verify it's not prefixed
        $collection2 = $loader->load('FOS\RestBundle\Tests\Fixtures\Controller\UsersController', 'rest');
        $nonPrefixedRoute = $collection2->get('get_users');
        $this->assertNotEquals('/aprefix/', substr($nonPrefixedRoute->getPath(), 0, 9));
    }

    /**
     * Test that conventional actions exist and are registered as GET methods.
     *
     * @see https://github.com/FriendsOfSymfony/RestBundle/issues/67
     */
    public function testConventionalActions()
    {
        $expectedMethod = ['GET'];
        $collection = $this->loadFromControllerFixture('UsersController');
        $subcollection = $this->loadFromControllerFixture('UserTopicsController');
        $subsubcollection = $this->loadFromControllerFixture('UserTopicCommentsController');

        // resource actions
        $this->assertEquals($expectedMethod, $collection->get('new_users')->getMethods());
        $this->assertEquals($expectedMethod, $collection->get('edit_user')->getMethods());
        $this->assertEquals($expectedMethod, $collection->get('remove_user')->getMethods());

        // subresource actions
        $this->assertEquals($expectedMethod, $collection->get('new_user_comments')->getMethods());
        $this->assertEquals($expectedMethod, $collection->get('edit_user_comment')->getMethods());
        $this->assertEquals($expectedMethod, $collection->get('remove_user_comment')->getMethods());

        // resource collection actions
        $this->assertEquals($expectedMethod, $subcollection->get('new_topics')->getMethods());
        $this->assertEquals($expectedMethod, $subcollection->get('edit_topic')->getMethods());
        $this->assertEquals($expectedMethod, $subcollection->get('remove_topic')->getMethods());

        // resource collection's resource collection actions
        $this->assertEquals($expectedMethod, $subsubcollection->get('new_comments')->getMethods());
        $this->assertEquals($expectedMethod, $subsubcollection->get('edit_comment')->getMethods());
        $this->assertEquals($expectedMethod, $subsubcollection->get('remove_comment')->getMethods());
    }

    /**
     * Test that custom actions (new/edit/remove) are dumped earlier,
     * and that developer routes order is kept.
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
     * Test if the routes are also working with uninflected words.
     *
     * @see https://github.com/FriendsOfSymfony/FOSRestBundle/pull/761
     */
    public function testMediaFixture()
    {
        $expectedMethod = ['GET'];
        $collection = $this->loadFromControllerFixture('MediaController');

        $this->assertCount(2, $collection->all());
        $this->assertEquals($expectedMethod, $collection->get('get_media')->getMethods());
        $this->assertEquals($expectedMethod, $collection->get('cget_media')->getMethods());
    }

    /**
     * Test if the routes are also working with uninflected words.
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
     * @see https://github.com/FriendsOfSymfony/FOSRestBundle/pull/879
     */
    public function testNameMethodPrefixIsPrependingCorrectly()
    {
        $collection = $this->loadFromControllerFixture('AnnotatedUsersController');

        $this->assertNotNull($collection->get('post_users_foo'), 'route for "post_users_foo" does not exist');
        $this->assertNotNull($collection->get('post_users_bar'), 'route for "post_users_bar" does not exist');
    }

    /**
     * @see https://github.com/FriendsOfSymfony/FOSRestBundle/issues/1507
     */
    public function testNameMethodPrefixIsPrependingCorrectlyWhenDefaultFalse()
    {
        $collection = $this->loadFromControllerFixture('AnnotatedUsersController', null, [], false);

        $this->assertNotNull($collection->get('post_users_foo'), 'route for "post_users_foo" does not exist');
        $this->assertNotNull($collection->get('post_users_bar'), 'route for "post_users_bar" does not exist');
    }

    /**
     * RestActionReader::getMethodArguments should ignore certain types of
     * parameters.
     */
    public function testRequestTypeHintsIgnoredCorrectly()
    {
        $collection = $this->loadFromControllerFixture('TypeHintedController');

        $this->assertNotNull($collection->get('get_articles'), 'route for "get_articles" does not exist');
        $this->assertEquals('/articles.{_format}', $collection->get('get_articles')->getPath());
        $this->assertNotNull($collection->get('post_articles'), 'route for "post_articles" does not exist');
        $this->assertEquals('/articles.{_format}', $collection->get('post_articles')->getPath());
        $this->assertNotNull($collection->get('get_article'), 'route for "get_article" does not exist');
        $this->assertEquals('/articles/{id}.{_format}', $collection->get('get_article')->getPath());
        $this->assertNotNull($collection->get('post_article'), 'route for "post_article" does not exist');
        $this->assertEquals('/articles/{id}.{_format}', $collection->get('post_article')->getPath());
    }

    /**
     * @see https://github.com/FriendsOfSymfony/FOSRestBundle/issues/1198
     */
    public function testParamConverterIsIgnoredInRouteGenerationCorrectly()
    {
        $collection = $this->loadFromControllerFixture('ParamConverterController');

        $this->assertNotNull($collection->get('post_something'), 'route for "post_something" does not exist');
        $this->assertSame('/somethings.{_format}', $collection->get('post_something')->getPath());
    }

    /**
     * Load routes collection from fixture class under Tests\Fixtures directory.
     *
     * @param string $fixtureName     name of the class fixture
     * @param string $namePrefix      route name prefix
     * @param array  $formats         resource formats available
     * @param bool   $hasMethodPrefix
     *
     * @return RestRouteCollection
     */
    protected function loadFromControllerFixture($fixtureName, $namePrefix = null, array $formats = [], $hasMethodPrefix = true)
    {
        $loader = $this->getControllerLoader($formats, $hasMethodPrefix);
        $loader->getControllerReader()->getActionReader()->setNamePrefix($namePrefix);

        return $loader->load('FOS\RestBundle\Tests\Fixtures\Controller\\'.$fixtureName, 'rest');
    }
}
