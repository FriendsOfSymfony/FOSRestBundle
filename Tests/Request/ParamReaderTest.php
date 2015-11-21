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

use Doctrine\Common\Annotations\AnnotationReader;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamReader;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * @NamePrefix("foo")
 * @QueryParam(name="bar")
 */
class FixtureClass
{
    /**
     * @QueryParam(name="foo", array=true)
     * @RequestParam(name="qux", map=true)
     */
    public function arrayOption()
    {
    }

    /**
     * @QueryParam(name="qux", default=false, description="null")
     * @RequestParam("foobar", requirements=@NotNull, strict=false)
     * @QueryParam(name="foo", nullable=true, description="foo")
     * @QueryParam("baz", incompatibles={"qux"}, description="null")
     */
    public function annotations()
    {
    }
}

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
        $bar = new QueryParam();
        $bar->name = 'bar';
        $this->classParams = [
            'bar' => $bar,
        ];
        $this->paramReader = new ParamReader(new AnnotationReader());
    }

    public function testLegacyArrayOption()
    {
        $annotations = $this->paramReader->read(new \ReflectionClass(__NAMESPACE__.'\FixtureClass'), 'arrayOption');

        $foo = new QueryParam();
        $foo->name = 'foo';
        $foo->map = true;

        $qux = new RequestParam();
        $qux->name = 'qux';
        $qux->array = true;

        $this->assertEquals(array_merge(
            $this->classParams,
            array('foo' => $foo, 'qux' => $qux)
        ), $annotations);
    }

    /**
     * Test that only QueryParam annotations are returned.
     */
    public function testReadsOnlyParamAnnotations()
    {
        $annotations = $this->paramReader->read(new \ReflectionClass(__NAMESPACE__.'\FixtureClass'), 'annotations');

        $this->assertCount(5, $annotations);

        foreach ($annotations as $name => $annotation) {
            $this->assertThat($annotation, $this->isInstanceOf('FOS\RestBundle\Controller\Annotations\ParamInterface'));
            $this->assertEquals($annotation->getName(), $name);
        }
    }

    public function testParameterAnnotations()
    {
        $annotations = $this->paramReader->read(new \ReflectionClass(__NAMESPACE__.'\FixtureClass'), 'annotations');

        $qux = new QueryParam();
        $qux->name = 'qux';
        $qux->default = false;
        $qux->description = 'null';

        $foobar = new RequestParam();
        $foobar->name = 'foobar';
        $foobar->requirements = new NotNull();
        $foobar->strict = false;

        $foo = new QueryParam();
        $foo->name = 'foo';
        $foo->nullable = true;
        $foo->description = 'foo';

        $baz = new QueryParam();
        $baz->name = 'baz';
        $baz->incompatibles = array('qux');
        $baz->description = 'null';

        $this->assertEquals(array_merge(
            $this->classParams,
            array(
                'qux' => $qux,
                'foobar' => $foobar,
                'foo' => $foo,
                'baz' => $baz,
            )
        ), $annotations);
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
