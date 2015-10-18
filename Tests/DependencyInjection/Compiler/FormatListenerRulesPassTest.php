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

use FOS\RestBundle\DependencyInjection\Compiler\FormatListenerRulesPass;

/**
 * @author Eduardo Gulias Davis <me@egulias.com>
 */
class FormatListenerRulesPassTest extends \PHPUnit_Framework_TestCase
{
    public function testRulesAreAddedWhenFormatListenerAndProfilerToolbarAreEnabled()
    {
        $definition = $this->getMock('Symfony\Component\DependencyInjection\Definition', ['addMethod']);

        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->setMethods(['hasDefinition', 'getDefinition', 'hasParameter', 'getParameter'])
            ->getMock();

        $container->expects($this->exactly(2))
            ->method('hasDefinition')
            ->will($this->returnValue(true));

        $container->expects($this->exactly(1))
            ->method('hasParameter')
            ->with('web_profiler.debug_toolbar.mode')
            ->will($this->returnValue(true));

        $container->expects($this->exactly(1))
            ->method('getParameter')
            ->willReturn(2);

        $container->expects($this->exactly(2))
            ->method('getDefinition')
            ->with($this->logicalOr(
                $this->equalTo('fos_rest.format_negotiator'),
                $this->equalTo('fos_rest.exception_format_negotiator')
            ))
            ->will($this->returnValue($definition));

        $compiler = new FormatListenerRulesPass();
        $compiler->process($container);
    }

    public function testNoRulesAreAddedWhenProfilerToolbarAreDisabled()
    {
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->setMethods(['hasDefinition', 'getDefinition', 'hasParameter'])
            ->getMock();

        $container->expects($this->exactly(1))
            ->method('hasDefinition')
            ->will($this->returnValue(true));

        $container->expects($this->exactly(1))
            ->method('hasParameter')
            ->with('web_profiler.debug_toolbar.mode')
            ->will($this->returnValue(false));

        $container->expects($this->never())
            ->method('getDefinition');

        $compiler = new FormatListenerRulesPass();
        $compiler->process($container);
    }
}
