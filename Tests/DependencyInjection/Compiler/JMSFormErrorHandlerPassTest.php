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

use FOS\RestBundle\DependencyInjection\Compiler\JMSFormErrorHandlerPass;
use JMS\Serializer\Handler\FormErrorHandler as JMSFormErrorHandler;
use FOS\RestBundle\Serializer\Normalizer\FormErrorHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class JmsFormErrorHandlerPassTest extends TestCase
{
    private $param = 'jms_serializer.form_error_handler.class';

    public function testParameterNotSetWhenParameterNotFound()
    {
        $container = $this->getMockBuilder(ContainerBuilder::class)->getMock();

        $container
            ->method('hasParameter')
            ->with($this->param)
            ->willReturn(false);

        $container
            ->expects($this->never())
            ->method('setParameter');

        $compiler = new JMSFormErrorHandlerPass();
        $compiler->process($container);
    }

    public function testParameterIsSetWhenValueIsDefaultClass()
    {
        $container = $this->getMockBuilder(ContainerBuilder::class)->getMock();

        $container
            ->method('hasParameter')
            ->with($this->param)
            ->willReturn(true);

        $container
            ->method('getParameter')
            ->with($this->param)
            ->willReturn(JMSFormErrorHandler::class);

        $container
            ->expects($this->exactly(1))
            ->method('setParameter')
            ->with($this->param, FormErrorHandler::class);

        $compiler = new JMSFormErrorHandlerPass();
        $compiler->process($container);
    }

    public function testParameterIsNotSetWhenValueIsNonDefaultClass()
    {
        $container = $this->getMockBuilder(ContainerBuilder::class)->getMock();

        $container
            ->method('hasParameter')
            ->with($this->param)
            ->willReturn(true);

        $container
            ->method('getParameter')
            ->with($this->param)
            ->willReturn('MyBundle\CustomFormErrorHandler');

        $container->expects($this->never())
                  ->method('setParameter');

        $compiler = new JMSFormErrorHandlerPass();
        $compiler->process($container);
    }
}
