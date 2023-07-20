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
use FOS\RestBundle\Controller\Annotations\ParamInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * AbstractParamTest.
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
class AbstractParamTest extends TestCase
{
    private $param;

    protected function setUp(): void
    {
        $this->param = $this->getMockForAbstractClass(AbstractParam::class);
    }

    public function testInterface()
    {
        $this->assertInstanceOf(ParamInterface::class, $this->param);
    }

    public function testDefaultValues()
    {
        $this->assertNull($this->param->name);
        $this->assertNull($this->param->key);
        $this->assertNull($this->param->default);
        $this->assertNull($this->param->description);
        $this->assertFalse($this->param->strict);
        $this->assertFalse($this->param->nullable);
        $this->assertEquals([], $this->param->incompatibles);
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
        $this->param->incompatibles = ['c', 'd'];
        $this->assertEquals(['c', 'd'], $this->param->getIncompatibilities());
    }

    public function testStrictGetter()
    {
        $this->param->strict = true;
        $this->assertTrue($this->param->isStrict());
    }

    public function testNotNullConstraint()
    {
        $this->assertEquals([new NotNull()], $this->param->getConstraints(''));

        $this->param->nullable = true;
        $this->assertEquals([], $this->param->getConstraints());
    }
}
