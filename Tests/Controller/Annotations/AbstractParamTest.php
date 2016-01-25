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

use FOS\RestBundle\Controller\Annotations;
use Symfony\Component\Validator\Constraints;

/**
 * AbstractParamTest.
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
class AbstractParamTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->param = $this->getMockForAbstractClass(Annotations\AbstractParam::class);
    }

    public function testInterface()
    {
        $this->assertInstanceOf(Annotations\ParamInterface::class, $this->param);
    }

    public function testDefaultValues()
    {
        $this->assertEquals(null, $this->param->name);
        $this->assertEquals(null, $this->param->key);
        $this->assertEquals(null, $this->param->default);
        $this->assertEquals(null, $this->param->description);
        $this->assertEquals(false, $this->param->strict);
        $this->assertEquals(false, $this->param->nullable);
        $this->assertEquals(array(), $this->param->incompatibles);
    }

    public function testNameGetter()
    {
        $this->param->name = 'Foo';
        $this->assertEquals('Foo', $this->param->getName());
    }

    public function testDefaultGetter()
    {
        $this->param->default = 'Bar';
        $this->assertEquals('Bar', $this->param->getDefault());

        $this->param->default = 'foo %parameter%';
        $this->assertEquals('foo %parameter%', $this->param->getDefault());
    }

    public function testDescriptionGetter()
    {
        $this->param->description = 'Bar';
        $this->assertEquals('Bar', $this->param->getDescription());
    }

    public function testIncompatiblesGetter()
    {
        $this->param->incompatibles = array('c', 'd');
        $this->assertEquals(array('c', 'd'), $this->param->getIncompatibilities());
    }

    public function testStrictGetter()
    {
        $this->param->strict = true;
        $this->assertTrue($this->param->isStrict());
    }

    public function testNotNullConstraint()
    {
        $this->assertEquals(array(new Constraints\NotNull()), $this->param->getConstraints(''));

        $this->param->nullable = true;
        $this->assertEquals(array(), $this->param->getConstraints());
    }
}
