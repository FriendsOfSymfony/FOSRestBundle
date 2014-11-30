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

use FOS\RestBundle\DependencyInjection\Compiler\ConfigurationCheckPass;

/**
 * ConfigurationCheckPass test
 *
 * @author Eriksen Costa <eriksencosta@gmail.com>
 */
class ConfigurationCheckPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \RuntimeException
     */
    public function testShouldThrowRuntimeExceptionWhenFOSRestBundleAnnotations()
    {
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->setMethods(array('has'))
            ->getMock();
        $container->expects($this->at(0))
            ->method('has')
            ->with($this->equalTo('sensio_framework_extra.view.listener'))
            ->will($this->returnValue(true));

        $container->expects($this->at(1))
            ->method('has')
            ->with($this->equalTo('fos_rest.view_response_listener'))
            ->will($this->returnValue(true));

        $compiler = new ConfigurationCheckPass();
        $compiler->process($container);
    }

    public function testShouldThrowRuntimeExceptionWhenBodyConverterIsEnabledButParamConvertersAreNotEnabled()
    {
        $this->setExpectedException(
            'RuntimeException',
            'You need to enable the parameter converter listeners in SensioFrameworkExtraBundle when using the FOSRestBundle RequestBodyParamConverter'
        );
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->setMethods(array('has'))
            ->getMock();
        $container->expects($this->at(1))
            ->method('has')
            ->with($this->equalTo('fos_rest.converter.request_body'))
            ->will($this->returnValue(true));

        $container->expects($this->at(2))
            ->method('has')
            ->with($this->equalTo('sensio_framework_extra.converter.listener'))
            ->will($this->returnValue(false));

        $compiler = new ConfigurationCheckPass();
        $compiler->process($container);
    }
}
