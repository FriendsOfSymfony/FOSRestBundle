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

use FOS\RestBundle\Controller\Annotations\AbstractScalarParam;
use FOS\RestBundle\Validator\Constraints\Regex;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * AbstractScalarParamTest.
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
class AbstractScalarParamTest extends TestCase
{
    protected function setUp(): void
    {
        $this->param = $this->getMockForAbstractClass(AbstractScalarParam::class);
    }

    public function testInterface(): void
    {
        $this->assertInstanceOf(AbstractScalarParam::class, $this->param);
    }

    public function testDefaultValues(): void
    {
        $this->assertNull($this->param->requirements);
        $this->assertFalse($this->param->map);
        $this->assertTrue($this->param->allowBlank);
    }

    public function testScalarConstraint(): void
    {
        $this->assertEquals([
            new NotNull(),
        ], $this->param->getConstraints());
    }

    public function testComplexRequirements(): void
    {
        $this->param->requirements = $requirement = $this->getMockBuilder(Constraint::class)->getMock();
        $this->assertEquals([
            new NotNull(),
            $requirement,
        ], $this->param->getConstraints());
    }

    public function testMultipleComplexRequirements(): void
    {
        $requirement1 = $this->getMockBuilder(Constraint::class)->getMock();
        $requirement2 = $this->getMockBuilder(Constraint::class)->getMock();
        $this->param->requirements = [$requirement1, $requirement2];

        $this->assertEquals([
            new NotNull(),
            $requirement1,
            $requirement2,
        ], $this->param->getConstraints());
    }

    public function testScalarRequirements(): void
    {
        $this->param->name = 'bar';
        $this->param->requirements = 'foo %bar% %%';
        $this->assertEquals([
            new NotNull(),
            new Regex([
                'pattern' => '#^(?:foo %bar% %%)$#xsu',
                'message' => "Parameter 'bar' value, does not match requirements 'foo %bar% %%'",
            ]),
        ], $this->param->getConstraints());
    }

    public function testArrayRequirements(): void
    {
        $this->param->requirements = [
            'rule' => 'foo',
            'error_message' => 'bar',
        ];
        $this->assertEquals([
            new NotNull(),
            new Regex([
                'pattern' => '#^(?:foo)$#xsu',
                'message' => 'bar',
            ]),
        ], $this->param->getConstraints());
    }

    public function testAllowBlank(): void
    {
        $this->param->allowBlank = false;
        $this->assertEquals([
            new NotNull(),
            new NotBlank(),
        ], $this->param->getConstraints());
    }

    public function testConstraintsTransformWhenParamIsAnArray(): void
    {
        $this->param->map = true;
        $this->assertEquals([new All([
            new NotNull(),
        ]), new NotNull()], $this->param->getConstraints());
    }

    public function testArrayWithBlankConstraintsWhenParamIsAnArray(): void
    {
        $this->param->map = true;
        $this->param->allowBlank = false;
        $this->assertEquals([new All([
            new NotNull(),
            new NotBlank(),
        ]), new NotNull()], $this->param->getConstraints());
    }

    public function testArrayWithNoConstraintsDoesNotCreateInvalidConstraint(): void
    {
        $this->param->nullable = true;
        $this->param->map = true;
        $this->assertEquals([new All([
            'constraints' => [],
        ])], $this->param->getConstraints());
    }
}
