<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\View;

use FOS\RestBundle\View\View;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * View test.
 *
 * @author Victor Berchet <victor@suumit.com>
 */
class ViewTest extends TestCase
{
    public function testSetLocation()
    {
        $url = 'users';
        $code = 500;

        $view = View::createRedirect($url, $code);
        $this->assertEquals($url, $view->getLocation());
        $this->assertEquals(null, $view->getRoute());
        $this->assertEquals($code, $view->getResponse()->getStatusCode());

        $view = new View();
        $location = 'location';
        $view->setLocation($location);
        $this->assertEquals($location, $view->getLocation());
    }

    public function testSetRoute()
    {
        $routeName = 'users';

        $view = View::createRouteRedirect($routeName, [], Response::HTTP_CREATED);
        $this->assertEquals(null, $view->getLocation());
        $this->assertEquals($routeName, $view->getRoute());
        $this->assertEquals(Response::HTTP_CREATED, $view->getResponse()->getStatusCode());

        $view->setLocation($routeName);
        $this->assertEquals($routeName, $view->getLocation());
        $this->assertEquals(null, $view->getRoute());

        $view = new View();
        $route = 'route';
        $view->setRoute($route);
        $this->assertEquals($route, $view->getRoute());
    }

    /**
     * @dataProvider setDataDataProvider
     */
    public function testSetData($data)
    {
        $view = new View();
        $view->setData($data);
        $this->assertEquals($data, $view->getData());
    }

    public static function setDataDataProvider()
    {
        return [
            'null as data' => [null],
            'array as data' => [['foo' => 'bar']],
        ];
    }

    public function testSetFormat()
    {
        $view = new View();
        $format = 'bar';
        $view->setFormat($format);
        $this->assertEquals($format, $view->getFormat());
    }

    /**
     * @dataProvider viewWithHeadersProvider
     */
    public function testSetHeaders()
    {
        $view = new View();
        $view->setHeaders(['foo' => 'bar']);

        $headers = $view->getResponse()->headers;
        $this->assertTrue($headers->has('foo'));
        $this->assertEquals('bar', $headers->get('foo'));
    }

    public function viewWithHeadersProvider()
    {
        return [
            [(new View())->setHeaders(['foo' => 'bar'])],
            [new View(null, null, ['foo' => 'bar'])],
        ];
    }

    public function testSetStatusCode()
    {
        $view = new View();
        $code = 404;
        $view->setStatusCode($code);
        $this->assertEquals($code, $view->getStatusCode());
        $this->assertEquals($code, $view->getResponse()->getStatusCode());
    }

    public function testGetStatusCodeFromResponse()
    {
        $view = new View();
        $this->assertNull($view->getStatusCode());
        $this->assertEquals(Response::HTTP_OK, $view->getResponse()->getStatusCode()); // default code of the response.
    }
}
