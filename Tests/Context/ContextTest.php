<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Context;

use FOS\RestBundle\Context\Context;
use JMS\Serializer\Exclusion\ExclusionStrategyInterface;
use PHPUnit\Framework\TestCase;

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
class ContextTest extends TestCase
{
    protected $context;

    protected function setUp(): void
    {
        $this->context = new Context();
    }

    public function testDefaultValues(): void
    {
        $this->assertEquals([], $this->context->getAttributes());
        $this->assertNull($this->context->getGroups());
    }

    public function testAttributes(): void
    {
        // Define attributes and check if that's the good return value.
        $this->assertEquals($this->context, $this->context->setAttribute('foo', 'bar'));
        $this->assertEquals($this->context, $this->context->setAttribute('foobar', 'foo'));

        $this->assertTrue($this->context->hasAttribute('foo'));
        $this->assertTrue($this->context->hasAttribute('foobar'));
        $this->assertFalse($this->context->hasAttribute('bar'));

        $this->assertEquals('bar', $this->context->getAttribute('foo'));
        $this->assertEquals('foo', $this->context->getAttribute('foobar'));

        $this->assertEquals(['foo' => 'bar', 'foobar' => 'foo'], $this->context->getAttributes());
    }

    public function testGroupAddition(): void
    {
        $this->context->addGroups(['quz', 'foo']);
        $this->context->addGroup('foo');
        $this->context->addGroup('bar');

        $this->assertEquals(['quz', 'foo', 'bar'], $this->context->getGroups());
    }

    public function testSetGroups(): void
    {
        $this->context->setGroups(['quz', 'foo']);

        $this->assertEquals(['quz', 'foo'], $this->context->getGroups());

        $this->context->setGroups(['foo']);
        $this->assertEquals(['foo'], $this->context->getGroups());
    }

    public function testAlreadyExistentGroupAddition(): void
    {
        $this->context->addGroup('foo');
        $this->context->addGroup('foo');
        $this->context->addGroup('bar');

        $this->assertEquals(['foo', 'bar'], $this->context->getGroups());
    }

    public function testVersion(): void
    {
        $this->context->setVersion('1.3.2');

        $this->assertEquals('1.3.2', $this->context->getVersion());
    }

    public function testEnableMaxDepth(): void
    {
        $this->context->enableMaxDepth();

        $this->assertTrue($this->context->isMaxDepthEnabled());
    }

    public function testDisableMaxDepth(): void
    {
        $this->context->disableMaxDepth();

        $this->assertFalse($this->context->isMaxDepthEnabled());
    }

    public function testSerializeNull(): void
    {
        $this->context->setSerializeNull(true);

        $this->assertTrue($this->context->getSerializeNull());
    }

    public function testExclusionStrategy(): void
    {
        $strategy1 = $this->getMockBuilder(ExclusionStrategyInterface::class)->getMock();
        $strategy2 = $this->getMockBuilder(ExclusionStrategyInterface::class)->getMock();

        $this->context->addExclusionStrategy($strategy1);
        $this->context->addExclusionStrategy($strategy2);

        $this->assertEquals([$strategy1, $strategy2], $this->context->getExclusionStrategies());
    }
}
