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

use FOS\RestBundle\Routing\Loader\DirectoryRouteLoader;
use FOS\RestBundle\Routing\Loader\RestRouteProcessor;
use Symfony\Component\Config\Loader\LoaderResolver;

class DirectoryRouteLoaderTest extends LoaderTest
{
    public function testLoad()
    {
        $collection = $this->loadFromDirectory(__DIR__.'/../../Fixtures/Controller/Directory');

        $this->assertCount(9, $collection);

        foreach ($collection as $route) {
            $this->assertInstanceOf('Symfony\Component\Routing\Route', $route);
        }
    }

    /**
     * @dataProvider supportsDataProvider
     */
    public function testSupports($resource, $type, $expected)
    {
        $loader = new DirectoryRouteLoader($this->getMockBuilder('Symfony\Component\Config\FileLocatorInterface')->getMock(), new RestRouteProcessor());

        if ($expected) {
            $this->assertTrue($loader->supports($resource, $type));
        } else {
            $this->assertFalse($loader->supports($resource, $type));
        }
    }

    public function supportsDataProvider()
    {
        return array(
            'existing-directory' => array(__DIR__.'/../../Fixtures/Controller', 'rest', true),
            'non-existing-directory' => array(__DIR__.'/Fixtures/Controller', 'rest', false),
            'class-name' => array('FOS\RestBundle\Tests\Fixtures\Controller\UsersController', 'rest', false),
            'null-type' => array(__DIR__.'/../../Fixtures/Controller', null, false),
        );
    }

    private function loadFromDirectory($resource)
    {
        $directoryLoader = new DirectoryRouteLoader($this->getMockBuilder('Symfony\Component\Config\FileLocatorInterface')->getMock(), new RestRouteProcessor());
        $controllerLoader = $this->getControllerLoader();

        // LoaderResolver sets the resolvers on the loaders passed to it
        new LoaderResolver(array($directoryLoader, $controllerLoader));

        return $directoryLoader->load($resource, 'rest');
    }
}
