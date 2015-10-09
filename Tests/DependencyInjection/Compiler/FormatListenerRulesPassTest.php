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
        $definition = $this->getMock('Symfony\Component\DependencyInjection\Definition', array('addMethod'));

        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->setMethods(array('hasDefinition', 'getDefinition', 'hasParameter', 'getParameter'))
            ->getMock();

        $container->expects($this->exactly(3))
            ->method('hasDefinition')
            ->will($this->returnValue(true));

        $container->expects($this->exactly(1))
            ->method('hasParameter')
            ->with('web_profiler.debug_toolbar.mode')
            ->will($this->returnValue(true));

        $container->expects($this->exactly(2))
            ->method('getParameter')
            ->will($this->onConsecutiveCalls(
                2,
                array(
                    array(
                        'host' => null,
                        'methods' => null,
                        'path' => '^/',
                        'priorities' => array('html', 'json'),
                        'fallback_format' => 'html',
                        'exception_fallback_format' => 'html',
                        'prefer_extension' => true,
                ),
            ))
        );

        $container->expects($this->exactly(4))
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
        $definition = $this->getMock('Symfony\Component\DependencyInjection\Definition', array('addMethod'));

        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->setMethods(array('hasDefinition', 'getDefinition', 'hasParameter', 'getParameter'))
            ->getMock();

        $container->expects($this->exactly(2))
            ->method('hasDefinition')
            ->will($this->returnValue(true));

        $container->expects($this->exactly(1))
            ->method('hasParameter')
            ->with('web_profiler.debug_toolbar.mode')
            ->will($this->returnValue(false));

        $container->expects($this->once())
            ->method('getParameter')
            ->with('fos_rest.format_listener.rules')
            ->will($this->returnValue(
                array(
                    array(
                        'host' => null,
                        'methods' => null,
                        'path' => '^/',
                        'priorities' => array('html', 'json'),
                        'fallback_format' => 'html',
                        'exception_fallback_format' => 'html',
                        'prefer_extension' => true,
                    ),
                ))
            );

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
}
