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

use FOS\RestBundle\Validator\Constraints\Regex;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints;

/**
 * AbstractScalarParamTest.
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
class AbstractScalarParamTest extends TestCase
{
    public function setUp()
    {
        $this->param = $this->getMockForAbstractClass('FOS\RestBundle\Controller\Annotations\AbstractScalarParam');
    }

    public function testInterface()
    {
        $this->assertInstanceOf('FOS\RestBundle\Controller\Annotations\AbstractParam', $this->param);
    }

    public function testDefaultValues()
    {
        $this->assertNull($this->param->requirements);
        $this->assertFalse($this->param->map);
        $this->assertTrue($this->param->allowBlank);
    }

    public function testScalarConstraint()
    {
        $this->assertEquals(array(
            new Constraints\NotNull(),
        ), $this->param->getConstraints());
    }

    public function testComplexRequirements()
    {
        $this->param->requirements = $requirement = $this->getMockBuilder('Symfony\Component\Validator\Constraint')->getMock();
        $this->assertEquals(array(
            new Constraints\NotNull(),
            $requirement,
        ), $this->param->getConstraints());
    }

    public function testScalarRequirements()
    {
        $this->param->name = 'bar';
        $this->param->requirements = 'foo %bar% %%';
        $this->assertEquals(array(
            new Constraints\NotNull(),
            new Regex(array(
                'pattern' => '#^(?:foo %bar% %%)$#xsu',
                'message' => "Parameter 'bar' value, does not match requirements 'foo %bar% %%'",
            )),
        ), $this->param->getConstraints());
    }

    public function testArrayRequirements()
    {
        $this->param->requirements = array(
            'rule' => 'foo',
            'error_message' => 'bar',
        );
        $this->assertEquals(array(
            new Constraints\NotNull(),
            new Regex(array(
                'pattern' => '#^(?:foo)$#xsu',
                'message' => 'bar',
            )),
        ), $this->param->getConstraints());
    }

    public function testAllowBlank()
    {
        $this->param->allowBlank = false;
        $this->assertEquals(array(
            new Constraints\NotNull(),
            new Constraints\NotBlank(),
        ), $this->param->getConstraints());
    }

    public function testConstraintsTransformWhenParamIsAnArray()
    {
        $this->param->map = true;
        $this->assertEquals(array(new Constraints\All(array(
            new Constraints\NotNull(),
        )), new Constraints\NotNull()), $this->param->getConstraints());
    }

    public function testArrayWithBlankConstraintsWhenParamIsAnArray()
    {
        $this->param->map = true;
        $this->param->allowBlank = false;
        $this->assertEquals(array(new Constraints\All(array(
            new Constraints\NotNull(),
            new Constraints\NotBlank(),
        )), new Constraints\NotNull()), $this->param->getConstraints());
    }

    public function testArrayWithNoConstraintsDoesNotCreateInvalidConstraint()
    {
        $this->param->nullable = true;
        $this->param->map = true;
        $this->assertEquals(array(new Constraints\All(array(
            'constraints' => [],
        ))), $this->param->getConstraints());
    }
}
