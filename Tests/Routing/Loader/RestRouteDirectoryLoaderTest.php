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
 * RestRouteDirectoryLoader test.
 *
 * @author Chad Sikorra <chad.sikorra@gmail.com>
 */
class RestRouteDirectoryLoaderTest extends LoaderTest
{
    /**
     * Test that it will support a directory but ignore a file
     */
    public function testLoaderSupport()
    {
        $loader = $this->getControllerDirectoryLoader();

        $this->assertfalse($loader->supports('FOS\RestBundle\Tests\Fixtures\Controller\UsersController', 'rest'));
        $this->assertTrue($loader->supports(__DIR__ . '/../../Fixtures/Controller', 'rest'));
    }

    /**
     * Test that all the controllers routes in the directory get loaded
     */
    public function testLoadControllerDirectory()
    {
        $loader = $this->getControllerDirectoryLoader();
        $collection = $loader->load(__DIR__ . '/../../Fixtures/Controller');

        $this->assertTrue($collection instanceof RouteCollection);
        $this->assertEquals(76, count($collection->all()));
    }
}
