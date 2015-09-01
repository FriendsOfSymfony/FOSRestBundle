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

use Symfony\Component\Validator\Constraints;

/**
 * AbstractParamTest.
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
class AbstractParamTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->param = $this->getMockForAbstractClass('FOS\RestBundle\Controller\Annotations\AbstractParam');
    }

    public function testInterface()
    {
        $this->assertInstanceOf('FOS\RestBundle\Controller\Annotations\ParamInterface', $this->param);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\ContainerAware', $this->param);
    }

    public function testDefaultValues()
    {
        $this->assertEquals(null, $this->param->name);
        $this->assertEquals(null, $this->param->key);
        $this->assertEquals(null, $this->param->default);
        $this->assertEquals(null, $this->param->description);
        $this->assertEquals(false, $this->param->strict);
        $this->assertEquals(false, $this->param->nullable);
        $this->assertEquals(array(), $this->param->incompatibles);
    }

    public function testNameGetter()
    {
        $this->param->name = 'Foo';
        $this->assertEquals('Foo', $this->param->getName());
    }

    public function testDefaultGetter()
    {
        $this->param->default = 'Bar';
        $this->assertEquals('Bar', $this->param->getDefault());

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->once())
            ->method('getParameter')
            ->with('parameter')
            ->willReturn('bar');

        $this->param->default = 'foo %parameter%';
        $this->param->setContainer($container);

        $this->assertEquals('foo bar', $this->param->getDefault());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage This param has been not initialized correctly. The container for parameter resolution is missing.
     */
    public function testDefaultGetterWhenContainerNotPassed()
    {
        $this->param->default = 'foo %bar% ';
        $this->param->getDefault();
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The container parameter "parameter", used in the controller parameters configuration value "foo %parameter%", must be a string or numeric, but it is of type object.
     */
    public function testInvalidContainerParameter()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->once())
            ->method('getParameter')
            ->with('parameter')
            ->willReturn(new \stdClass());

        $this->param->default = 'foo %parameter%';
        $this->param->setContainer($container);

        $this->param->getDefault();
    }

    public function testDescriptionGetter()
    {
        $this->param->description = 'Bar';
        $this->assertEquals('Bar', $this->param->getDescription());
    }

    public function testIncompatiblesGetter()
    {
        $this->param->incompatibles = array('c', 'd');
        $this->assertEquals(array('c', 'd'), $this->param->getIncompatibilities());
    }

    public function testStrictGetter()
    {
        $this->param->strict = true;
        $this->assertTrue($this->param->isStrict());
    }

    public function testNotNullConstraint()
    {
        $this->assertEquals(array(new Constraints\NotNull()), $this->param->getConstraints(''));

        $this->param->nullable = true;
        $this->assertEquals(array(), $this->param->getConstraints());
    }
}
