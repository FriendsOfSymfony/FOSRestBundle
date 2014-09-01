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
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Request\ParamReader;

/**
 * QueryParamReader test.
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

        $methodAnnotations = array();
        $foo = new QueryParam();
        $foo->name = 'foo';
        $foo->requirements = '\d+';
        $foo->description = 'The foo';
        $methodAnnotations[] = $foo;

        $bar = new QueryParam();
        $bar->name = 'bar';
        $bar->requirements = '\d+';
        $bar->description = 'The bar';
        $methodAnnotations[] = $bar;

        $methodAnnotations[] = new NamePrefix(array());

        $annotationReader
            ->expects($this->any())
            ->method('getMethodAnnotations')
            ->will($this->returnValue($methodAnnotations));

        $classAnnotations = array();

        $baz = new QueryParam();
        $baz->name = 'baz';
        $baz->requirements = '\d+';
        $baz->description = 'The baz';
        $classAnnotations[] = $baz;

        $mikz = new QueryParam();
        $mikz->name = 'mikz';
        $mikz->requirements = '\d+';
        $mikz->description = 'The real mikz';
        $classAnnotations[] = $mikz;

        $not = new NamePrefix(array());
        $classAnnotations[] = $not;

        $annotationReader
                ->expects($this->any())
                ->method('getClassAnnotations')
                ->will($this->returnValue($classAnnotations));

        $this->paramReader = new ParamReader($annotationReader);
    }

    /**
     * Test that only QueryParam annotations are returned.
     */
    public function testReadsOnlyParamAnnotations()
    {
        $annotations = $this->paramReader->read(new \ReflectionClass(__CLASS__), 'setup');

        $this->assertCount(4, $annotations);

        foreach ($annotations as $name => $annotation) {
            $this->assertThat($annotation, $this->isInstanceOf('FOS\RestBundle\Controller\Annotations\Param'));
            $this->assertEquals($annotation->name, $name);
        }
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Class 'FOS\RestBundle\Tests\Request\ParamReaderTest' has no method 'foo' method.
     */
    public function testExceptionOnNonExistingMethod()
    {
        $this->paramReader->read(new \ReflectionClass(__CLASS__), 'foo');
    }
}
