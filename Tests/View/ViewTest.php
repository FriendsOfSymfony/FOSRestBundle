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
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\HttpFoundation\Response;

/**
 * View test.
 *
 * @author Victor Berchet <victor@suumit.com>
 */
class ViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetTemplateTemplateFormat()
    {
        $view = new View();

        $view->setTemplate('foo');
        $this->assertEquals('foo', $view->getTemplate());

        $view->setTemplate($template = new TemplateReference());
        $this->assertEquals($template, $view->getTemplate());

        $view->setTemplate([]);
    }

    public function testSetLocation()
    {
        $url = 'users';
        $code = 500;

        $view = View::createRedirect($url, $code);
        $this->assertAttributeEquals($url, 'location', $view);
        $this->assertAttributeEquals(null, 'route', $view);
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
        $this->assertAttributeEquals($routeName, 'route', $view);
        $this->assertAttributeEquals(null, 'location', $view);
        $this->assertEquals(Response::HTTP_CREATED, $view->getResponse()->getStatusCode());

        $view->setLocation($routeName);
        $this->assertAttributeEquals($routeName, 'location', $view);
        $this->assertAttributeEquals(null, 'route', $view);

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

    /**
     * @dataProvider setTemplateDataDataProvider
     */
    public function testSetTemplateData($templateData)
    {
        $view = new View();
        $view->setTemplateData($templateData);
        $this->assertEquals($templateData, $view->getTemplateData());
    }

    public static function setTemplateDataDataProvider()
    {
        return [
            'null as data' => [null],
            'array as data' => [['foo' => 'bar']],
            'function as data' => [function () {}],
        ];
    }

    public function testSetEngine()
    {
        $view = new View();
        $engine = 'bar';
        $view->setEngine($engine);
        $this->assertEquals($engine, $view->getEngine());
    }

    public function testSetFormat()
    {
        $view = new View();
        $format = 'bar';
        $view->setFormat($format);
        $this->assertEquals($format, $view->getFormat());
    }

    public function testSetHeaders()
    {
        $view = new View();
        $headers = ['foo' => 'bar'];
        $expected = ['foo' => ['bar'], 'cache-control' => ['no-cache']];
        $view->setHeaders($headers);
        $this->assertEquals($expected, $view->getHeaders());
    }

    public function testHeadersInConstructorAreAssignedToResponseObject()
    {
        $headers = ['foo' => 'bar'];
        $expected = ['foo' => ['bar'], 'cache-control' => ['no-cache']];
        $view = new View(null, null, $headers);
        $this->assertEquals($expected, $view->getHeaders());
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
