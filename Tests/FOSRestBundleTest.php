<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests;

use FOS\RestBundle\FOSRestBundle;

/**
 * FOSRestBundle test
 *
 * @author Eriksen Costa <eriksencosta@gmail.com>
 */
class FOSRestBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerBuilder');
        $container->expects($this->once())
            ->method('addCompilerPass')
            ->with($this->isInstanceOf('\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface'));

        $bundle = new FOSRestBundle();
        $bundle->build($container);
    }
}
