<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Request;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Request\ParamReader;

/**
 * ParamReader test.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class ParamReaderTest extends \PHPUnit_Framework_TestCase
{
    private $paramReader;

    /**
     * Test setup.
     */
    public function setup()
    {
        $annotationReader = $this->getMock('Doctrine\Common\Annotations\Reader');

        $methodAnnotations = [];
        $foo = $this->createMockedParam();
        $foo
            ->expects($this->any())
            ->method('getName')
            ->willReturn('foo');
        $methodAnnotations[] = $foo;

        $bar = $this->createMockedParam();
        $bar
            ->expects($this->any())
            ->method('getName')
            ->willReturn('bar');
        $methodAnnotations[] = $bar;

        $methodAnnotations[] = new NamePrefix([]);

        $annotationReader
            ->expects($this->any())
            ->method('getMethodAnnotations')
            ->will($this->returnValue($methodAnnotations));

        $classAnnotations = [];

        $baz = $this->createMockedParam();
        $baz
            ->expects($this->any())
            ->method('getName')
            ->willReturn('baz');
        $classAnnotations[] = $baz;

        $mikz = $this->createMockedParam();
        $mikz
            ->expects($this->any())
            ->method('getName')
            ->willReturn('micz');
        $classAnnotations[] = $mikz;

        $classAnnotations[] = new NamePrefix([]);

        $annotationReader
                ->expects($this->any())
                ->method('getClassAnnotations')
                ->will($this->returnValue($classAnnotations));

        $this->paramReader = new ParamReader($annotationReader);
    }

    /**
     * Test that only ParamInterface annotations are returned.
     */
    public function testReadsOnlyParamAnnotations()
    {
        $annotations = $this->paramReader->read(new \ReflectionClass(__CLASS__), 'setup');

        $this->assertCount(4, $annotations);

        foreach ($annotations as $name => $annotation) {
            $this->assertInstanceOf('FOS\RestBundle\Controller\Annotations\ParamInterface', $annotation);
            $this->assertEquals($annotation->getName(), $name);
        }
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Class 'FOS\RestBundle\Tests\Request\ParamReaderTest' has no method 'foo'.
     */
    public function testExceptionOnNonExistingMethod()
    {
        $this->paramReader->read(new \ReflectionClass(__CLASS__), 'foo');
    }

    protected function createMockedParam()
    {
        return $this->getMock('FOS\RestBundle\Controller\Annotations\ParamInterface');
    }
}
