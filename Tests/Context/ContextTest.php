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

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
class ContextTest extends \PHPUnit_Framework_TestCase
{
    protected $context;

    public function setUp()
    {
        $this->context = new Context();
    }

    public function testDefaultValues()
    {
        $this->assertEquals([], $this->context->getAttributes());
        $this->assertEquals(null, $this->context->getGroups());
    }

    public function testAttributes()
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

    public function testGroupAddition()
    {
        $this->context->addGroups(array('quz', 'foo'));
        $this->context->addGroup('foo');
        $this->context->addGroup('bar');

        $this->assertEquals(['quz', 'foo', 'bar'], $this->context->getGroups());
    }

    public function testSetGroups()
    {
        $this->context->setGroups(array('quz', 'foo'));

        $this->assertEquals(array('quz', 'foo'), $this->context->getGroups());

        $this->context->setGroups(array('foo'));
        $this->assertEquals(array('foo'), $this->context->getGroups());
    }

    public function testAlreadyExistentGroupAddition()
    {
        $this->context->addGroup('foo');
        $this->context->addGroup('foo');
        $this->context->addGroup('bar');

        $this->assertEquals(array('foo', 'bar'), $this->context->getGroups());
    }

    public function testVersion()
    {
        $this->context->setVersion('1.3.2');

        $this->assertEquals('1.3.2', $this->context->getVersion());
    }

    /**
     * @group legacy
     */
    public function testMaxDepth()
    {
        $this->context->setMaxDepth(10);

        $this->assertEquals(10, $this->context->getMaxDepth());
    }

    public function testSerializeNull()
    {
        $this->context->setSerializeNull(true);

        $this->assertEquals(true, $this->context->getSerializeNull());
    }

    public function testExclusionStrategy()
    {
        $strategy1 = $this->getMockBuilder(ExclusionStrategyInterface::class)->getMock();
        $strategy2 = $this->getMockBuilder(ExclusionStrategyInterface::class)->getMock();

        $this->context->addExclusionStrategy($strategy1);
        $this->context->addExclusionStrategy($strategy2);

        $this->assertEquals([$strategy1, $strategy2], $this->context->getExclusionStrategies());
    }
}
