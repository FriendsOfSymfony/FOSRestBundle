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

use Symfony\Component\Config\Loader\LoaderResolver,
    Symfony\Component\Config\FileLocator;

use FOS\RestBundle\Routing\Loader\RestRouteProcessor,
    FOS\RestBundle\Routing\Loader\RestXmlCollectionLoader;

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
        $collection     = $this->loadFromXmlCollectionFixture('users_collection.xml');
        $etalonRoutes   = $this->loadEtalonRoutesInfo('users_collection.yml');

        foreach ($etalonRoutes as $name => $params) {
            $route = $collection->get($name);

            $this->assertNotNull($route, $name);
            $this->assertEquals($params['pattern'], $route->getPattern(), $name);
            $this->assertEquals($params['method'], $route->getRequirement('_method'), $name);
            $this->assertContains($params['controller'], $route->getDefault('_controller'), $name);
        }
    }

    /**
     * Test that XML collection with custom prefixes gets parsed correctly.
     */
    public function testPrefixedUsersFixture()
    {
        $collection     = $this->loadFromXmlCollectionFixture('prefixed_users_collection.xml');
        $etalonRoutes   = $this->loadEtalonRoutesInfo('prefixed_users_collection.yml');

        foreach ($etalonRoutes as $name => $params) {
            $route = $collection->get($name);

            $this->assertNotNull($route, $name);
            $this->assertEquals($params['pattern'], $route->getPattern(), $name);
            $this->assertEquals($params['method'], $route->getRequirement('_method'), $name);
            $this->assertContains($params['controller'], $route->getDefault('_controller'), $name);
        }
    }

    /**
     * Load routes collection from XML fixture routes under Tests\Fixtures directory.
     *
     * @param   string  $fixtureName    name of the class fixture
     */
    protected function loadFromXmlCollectionFixture($fixtureName)
    {
        $collectionLoader = new RestXmlCollectionLoader(
            new FileLocator(array(__DIR__ . '/../../Fixtures/Routes')),
            new RestRouteProcessor()
        );
        $controllerLoader = $this->getControllerLoader();

        $resolver = new LoaderResolver(array($collectionLoader, $controllerLoader));

        return $collectionLoader->load($fixtureName, 'rest');
    }
}
