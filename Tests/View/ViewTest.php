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

use FOS\RestBundle\View\View,
    FOS\RestBundle\View\RedirectView,
    FOS\RestBundle\View\RouteRedirectView,
    Symfony\Bundle\FrameworkBundle\Templating\TemplateReference,
    FOS\Rest\Util\Codes,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

/**
 * View test
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

        $view->setTemplate(array());
    }

    public function testSetLocation()
    {
        $url = 'users';
        $code = 500;

        $view = RedirectView::create($url, $code);
        $this->assertAttributeEquals($url, 'location', $view);
        $this->assertAttributeEquals(null, 'route', $view);
        $this->assertAttributeEquals($code, 'statusCode', $view);

        $view = new View();
        $location = 'location';
        $view->setLocation($location);
        $this->assertEquals($location, $view->getLocation());
    }

    public function testSetRoute()
    {
        $routeName = 'users';

        $view = RouteRedirectView::create($routeName);
        $this->assertAttributeEquals($routeName, 'route', $view);
        $this->assertAttributeEquals(null, 'location', $view);
        $this->assertAttributeEquals(Codes::HTTP_CREATED, 'statusCode', $view);

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
        return array(
            'null as data' => array(null),
            'array as data' => array(array('foo' => 'bar')),
        );
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
        $headers = array('foo' => 'bar');
        $view->setHeaders($headers);
        $this->assertEquals($headers, $view->getHeaders());
    }

    public function testSetStatusCode()
    {
        $view = new View();
        $code = 404;
        $view->setStatusCode($code);
        $this->assertEquals($code, $view->getStatusCode());
    }
}
