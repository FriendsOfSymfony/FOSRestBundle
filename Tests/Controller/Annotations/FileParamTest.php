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

use FOS\RestBundle\Controller\Annotations\AbstractParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * FileParamTest.
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
class FileParamTest extends TestCase
{
    protected function setUp(): void
    {
        $this->param = $this->getMockBuilder(FileParam::class)
            ->setMethods(['getKey'])
            ->getMock();
    }

    public function testInterface(): void
    {
        $this->assertInstanceOf(AbstractParam::class, $this->param);
    }

    public function testValueGetter(): void
    {
        $this->param
            ->expects($this->once())
            ->method('getKey')
            ->willReturn('foo');

        $request = $this->getMockBuilder(Request::class)->getMock();
        $parameterBag = $this->getMockBuilder(ParameterBag::class)->getMock();
        $parameterBag
            ->expects($this->once())
            ->method('get')
            ->with('foo', 'bar')
            ->willReturn('foobar');
        $request->files = $parameterBag;

        $this->assertEquals('foobar', $this->param->getValue($request, 'bar'));
    }

    public function testComplexRequirements(): void
    {
        $this->param->requirements = $requirement = $this->getMockBuilder(Constraint::class)->getMock();
        $this->assertEquals([
            new NotNull(),
            $requirement,
            new File(),
        ], $this->param->getConstraints());
    }

    public function testFileRequirements(): void
    {
        $this->param->nullable = true;
        $this->param->requirements = $requirements = ['mimeTypes' => 'application/json'];
        $this->assertEquals([
            new File($requirements),
        ], $this->param->getConstraints());
    }

    public function testImageRequirements(): void
    {
        $this->param->image = true;
        $this->param->requirements = $requirements = ['mimeTypes' => 'image/gif'];
        $this->assertEquals([
            new NotNull(),
            new Image($requirements),
        ], $this->param->getConstraints());
    }

    public function testImageConstraintsTransformWhenParamIsAnArray(): void
    {
        $this->param->image = true;
        $this->param->map = true;
        $this->param->requirements = $requirements = ['mimeTypes' => 'image/gif'];
        $this->assertEquals([new All([
            new NotNull(),
            new Image($requirements),
        ])], $this->param->getConstraints());
    }

    public function testFileConstraintsWhenParamIsAnArray(): void
    {
        $this->param->map = true;
        $this->param->requirements = $requirements = ['mimeTypes' => 'application/pdf'];
        $this->assertEquals([new All([
            new NotNull(),
            new File($requirements),
        ])], $this->param->getConstraints());
    }
}
