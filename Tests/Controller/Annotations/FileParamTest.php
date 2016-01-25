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
 * FileParamTest.
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
class FileParamTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->param = $this->getMock('FOS\RestBundle\Controller\Annotations\FileParam', array(
            'getKey',
        ));
    }

    public function testInterface()
    {
        $this->assertInstanceOf('FOS\RestBundle\Controller\Annotations\AbstractParam', $this->param);
    }

    public function testValueGetter()
    {
        $this->param
            ->expects($this->once())
            ->method('getKey')
            ->willReturn('foo');

        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $parameterBag = $this->getMock('Symfony\Component\HttpFoundation\ParameterBag');
        $parameterBag
            ->expects($this->once())
            ->method('get')
            ->with('foo', 'bar')
            ->willReturn('foobar');
        $request->files = $parameterBag;

        $this->assertEquals('foobar', $this->param->getValue($request, 'bar'));
    }

    public function testComplexRequirements()
    {
        $this->param->requirements = $requirement = $this->getMock('Symfony\Component\Validator\Constraint');
        $this->assertEquals(array(
            new Constraints\NotNull(),
            $requirement,
            new Constraints\File(),
        ), $this->param->getConstraints());
    }

    public function testFileRequirements()
    {
        $this->param->nullable = true;
        $this->param->requirements = $requirements = ['mimeTypes' => 'application/json'];
        $this->assertEquals(array(
            new Constraints\File($requirements),
        ), $this->param->getConstraints());
    }

    public function testImageRequirements()
    {
        $this->param->image = true;
        $this->param->requirements = $requirements = ['mimeTypes' => 'image/gif'];
        $this->assertEquals(array(
            new Constraints\NotNull(),
            new Constraints\Image($requirements),
        ), $this->param->getConstraints());
    }
}
