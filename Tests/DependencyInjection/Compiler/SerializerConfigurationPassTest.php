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
 * SerializerConfigurationPassTest test.
 */
class SerializerConfigurationPassTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldDoNothingIfSerializerIsFound()
    {
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->setMethods(['has'])
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
            ->setMethods(['has'])
            ->getMock();

        $container->method('has')
            ->will($this->returnValueMap([
                ['fos_rest.serializer', false],
                ['jms_serializer.serializer', false],
                ['serializer', false], ]));

        $compiler = new SerializerConfigurationPass();
        $compiler->process($container);
    }

    public function testShouldConfigureJMSSerializer()
    {
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->setMethods(['has', 'setAlias', 'removeDefinition'])
            ->getMock();

        $container->method('has')
            ->will($this->returnValueMap([
                ['fos_rest.serializer', false],
                ['jms_serializer.serializer', true],
                ['serializer', true],
            ]));

        $container->expects($this->exactly(2))
            ->method('setAlias')
            ->withConsecutive(
                [$this->equalTo('fos_rest.serializer'), $this->equalTo('jms_serializer.serializer')],
                [$this->equalTo('fos_rest.serializer'), $this->equalTo('serializer')]
            );

        $compiler = new SerializerConfigurationPass();
        $compiler->process($container);
    }

    public function testShouldConfigureCoreSerializer()
    {
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->setMethods(['has', 'setAlias', 'removeDefinition'])
            ->getMock();

        $container->method('has')
            ->will($this->returnValueMap([
                ['fos_rest.serializer', false],
                ['jms_serializer.serializer', false],
                ['serializer', true], ]));

        $container->expects($this->once())
            ->method('setAlias')
            ->with($this->equalTo('fos_rest.serializer'), $this->equalTo('serializer'));

        $container->expects($this->once())
            ->method('removeDefinition')
            ->with('fos_rest.serializer.exception_wrapper_serialize_handler');

        $compiler = new SerializerConfigurationPass();
        $compiler->process($container);
    }
}
