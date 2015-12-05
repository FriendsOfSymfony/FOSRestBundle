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

use FOS\RestBundle\Controller\Annotations\ParamInterface;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Request\ParamReader;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Validator\Constraints\NotNull;

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
        $annotationReader = $this->getMock(AnnotationReader::class, array());

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
            $this->assertInstanceOf(ParamInterface::class, $annotation);
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

    public function testAnnotationReader()
    {
        $reader = new AnnotationReader();

        $method = new \ReflectionMethod('FOS\RestBundle\Tests\Fixtures\Controller\ParamsAnnotatedController', 'getArticlesAction');
        $params = $reader->getMethodAnnotations($method);

        // Param 1 (query)
        $this->assertEquals('page', $params[0]->name);
        $this->assertEquals('\\d+', $params[0]->requirements);
        $this->assertEquals('1', $params[0]->default);
        $this->assertEquals('Page of the overview.', $params[0]->description);
        $this->assertFalse($params[0]->map);
        $this->assertFalse($params[0]->strict);

        // Param 2 (request)
        $this->assertEquals('byauthor', $params[1]->name);
        $this->assertEquals('[a-z]+', $params[1]->requirements);
        $this->assertEquals('by author', $params[1]->description);
        $this->assertEquals(['search'], $params[1]->incompatibles);
        $this->assertFalse($params[1]->map);
        $this->assertTrue($params[1]->strict);

        // Param 3 (query)
        $this->assertEquals('filters', $params[2]->name);
        $this->assertTrue($params[2]->map);
        $this->assertEquals(new NotNull(), $params[2]->requirements);

        // Param 4 (file)
        $this->assertEquals('avatar', $params[3]->name);
        $this->assertEquals(['mimeTypes' => 'application/json'], $params[3]->requirements);
        $this->assertTrue($params[3]->image);
        $this->assertTrue($params[3]->strict);

        // Param 5 (file)
        $this->assertEquals('foo', $params[4]->name);
        $this->assertEquals(new NotNull(), $params[4]->requirements);
        $this->assertFalse($params[4]->image);
        $this->assertFalse($params[4]->strict);
    }

    protected function createMockedParam()
    {
        return $this->getMock(ParamInterface::class);
    }
}
