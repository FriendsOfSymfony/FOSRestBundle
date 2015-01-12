<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\DependencyInjection\Compiler;

use FOS\RestBundle\DependencyInjection\Compiler\SerializerConfigurationPass;

/**
 * SerializerConfigurationPassTest test
 */
class SerializerConfigurationPassTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldDoNothingIfSerializerIsFound()
    {
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->setMethods(array('has'))
            ->getMock();

        $container->expects($this->once())
            ->method('has')
            ->with($this->equalTo('fos_rest.serializer'))
            ->will($this->returnValue(true));

        $container->expects($this->never())
            ->method('setAlias');

        $compiler = new SerializerConfigurationPass();
        $compiler->process($container);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testShouldThrowInvalidArgumentExceptionWhenNoSerializerIsFound()
    {
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->setMethods(array('has'))
            ->getMock();

        $container->method('has')
            ->will($this->returnValueMap(array(
                array('fos_rest.serializer', false),
                array('jms_serializer.serializer', false),
                array('serializer', false))));

        $compiler = new SerializerConfigurationPass();
        $compiler->process($container);
    }

    public function testShouldConfigureJMSSerializer()
    {
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->setMethods(array('has', 'setAlias'))
            ->getMock();

        $container->method('has')
            ->will($this->returnValueMap(array(
                array('fos_rest.serializer', false),
                array('jms_serializer.serializer', true),
                array('serializer', true))));


        $container->expects($this->once())
            ->method('setAlias')
            ->with($this->equalTo('fos_rest.serializer'), $this->equalTo('jms_serializer.serializer'));

        $compiler = new SerializerConfigurationPass();
        $compiler->process($container);
    }

    public function testShouldConfigureCoreSerializer()
    {
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->setMethods(array('has', 'setAlias'))
            ->getMock();

        $container->method('has')
            ->will($this->returnValueMap(array(
                array('fos_rest.serializer', false),
                array('jms_serializer.serializer', false),
                array('serializer', true))));


        $container->expects($this->once())
            ->method('setAlias')
            ->with($this->equalTo('fos_rest.serializer'), $this->equalTo('serializer'));

        $compiler = new SerializerConfigurationPass();
        $compiler->process($container);
    }
}
