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

use Symfony\Component\DependencyInjection\ContainerBuilder;

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
        $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerBuilder');
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
}
