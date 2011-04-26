<?php

namespace FOS\RestBundle\Tests\Routing\Loader;

use Symfony\Component\Config\Loader\LoaderResolver,
    Symfony\Component\Config\FileLocator;

use FOS\RestBundle\Routing\Loader\RestRouteLoader,
    FOS\RestBundle\Routing\Loader\RestYamlCollectionLoader;

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
        $collection     = $this->loadFromYamlCollectionFixture('users_collection.yml');
        $etalonRoutes   = $this->loadEtalonRoutesInfo('users_collection.yml');

        foreach ($etalonRoutes as $name => $params) {
            $route = $collection->get($name);

            $this->assertNotNull($route);
            $this->assertEquals($params['pattern'], $route->getPattern());
            $this->assertEquals($params['method'], $route->getRequirement('_method'));
            $this->assertContains($params['controller'], $route->getDefault('_controller'));
        }
    }

    /**
     * Test that YAML collection with custom prefixes gets parsed correctly.
     */
    public function testPrefixedUsersFixture()
    {
        $collection     = $this->loadFromYamlCollectionFixture('prefixed_users_collection.yml');
        $etalonRoutes   = $this->loadEtalonRoutesInfo('prefixed_users_collection.yml');

        foreach ($etalonRoutes as $name => $params) {
            $route = $collection->get($name);

            $this->assertNotNull($route);
            $this->assertEquals($params['pattern'], $route->getPattern());
            $this->assertEquals($params['method'], $route->getRequirement('_method'));
            $this->assertContains($params['controller'], $route->getDefault('_controller'));
        }
    }

    /**
     * Load routes collection from YAML fixture routes under Tests\Fixtures directory.
     *
     * @param   string  $fixtureName    name of the class fixture
     */
    protected function loadFromYamlCollectionFixture($fixtureName)
    {
        $collectionLoader = new RestYamlCollectionLoader(new FileLocator(
            array(__DIR__ . '/../../Fixtures/Routes')
        ));
        $controllerLoader = $this->getControllerLoader();

        $resolver = new LoaderResolver(array($collectionLoader, $controllerLoader));

        return $collectionLoader->load($fixtureName, 'rest');
    }
}
