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
 * Class FormatListenerRulesPass
 * @author Eduardo Gulias Davis <me@egulias.com>
 */
class FormatListenerRulesPassTest extends \PHPUnit_Framework_TestCase
{
    const EXTENSION_ALIAS = 'fos_rest_test';

    public function testRulesAreAddedWhenFormatListenerAndProfilerToolbarAreEnabled()
    {
        $definition = $this->getMock('Symfony\Component\DependencyInjection\Definition', array('addMethod'));

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $container->expects($this->at(1))
            ->method('getExtensionConfig')
            ->with(self::EXTENSION_ALIAS)
            ->will($this->returnValue(array(array('format_listener' => array('rules' => array())))));

        $container->expects($this->at(2))
            ->method('getExtensionConfig')
            ->with('web_profiler')
            ->will($this->returnValue(array(array('toolbar' => true, 'intercept_redirects' => false))));

        $container->expects($this->exactly(3))
            ->method('hasDefinition')
            ->will($this->returnValue(true));

        $container->expects($this->exactly(2))
            ->method('getDefinition')
            ->with(self::EXTENSION_ALIAS.'.format_negotiator')
            ->will($this->returnValue($definition));

        $compiler = new FormatListenerRulesPass(self::EXTENSION_ALIAS);
        $compiler->process($container);
    }

    public function testNoRulesAreAddedWhenProfilerToolbarAreDisabled()
    {
        $definition = $this->getMock('Symfony\Component\DependencyInjection\Definition');
        $definition->method('addMethodCall');

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $container->expects($this->once())->method('hasDefinition')->will($this->returnValue(true));
        $container->expects($this->at(1))
            ->method('getExtensionConfig')
            ->with(self::EXTENSION_ALIAS)
            ->will($this->returnValue(array(array('format_listener' => array('rules' => array())))));

        $container->expects($this->at(2))
            ->method('getExtensionConfig')
            ->with('web_profiler')
            ->will($this->returnValue(array(array('toolbar' => false, 'intercept_redirects' => false))));

        $container->expects($this->never())->method('getDefinition');

        $compiler = new FormatListenerRulesPass(self::EXTENSION_ALIAS);
        $compiler->process($container);
    }
}
