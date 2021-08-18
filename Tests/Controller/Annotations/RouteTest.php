<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Controller\Annotations;

use FOS\RestBundle\Controller\Annotations\Route;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    public function testCanInstantiate()
    {
        $path = '/path';
        $name = 'route_name';
        $requirements = ['locale' => 'en'];
        $options = ['compiler_class' => 'RouteCompiler'];
        $defaults = ['_controller' => 'MyBlogBundle:Blog:index'];
        $host = '{locale}.example.com';
        $methods = ['GET', 'POST'];
        $schemes = ['https'];
        $condition = 'context.getMethod() == "GET"';

        $route = new Route(
            $path,
            null,
            $name,
            $requirements,
            $options,
            $defaults,
            $host,
            $methods,
            $schemes,
            $condition
        );

        $this->assertEquals($path, $route->getPath());
        $this->assertEquals($name, $route->getName());
        $this->assertEquals($requirements, $route->getRequirements());
        $this->assertEquals($options, $route->getOptions());
        $this->assertEquals($defaults, $route->getDefaults());
        $this->assertEquals($host, $route->getHost());
        $this->assertEquals($methods, $route->getMethods());
        $this->assertEquals($schemes, $route->getSchemes());
        $this->assertEquals($condition, $route->getCondition());
    }
}
