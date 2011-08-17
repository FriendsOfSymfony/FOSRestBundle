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
    Symfony\Bundle\FrameworkBundle\Templating\TemplateReference,
    FOS\RestBundle\Response\Codes,
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
        $view = new View();
        $location = 'location';
        $view->setLocation($location);
        $this->assertEquals($location, $view->getLocation());
    }

    public function testSetRoute()
    {
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
}
