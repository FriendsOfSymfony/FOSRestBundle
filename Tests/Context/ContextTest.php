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

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
class ContextTest extends \PHPUnit_Framework_TestCase
{
    protected $context;

    public function setUp()
    {
        $this->context = $this->getMock('FOS\RestBundle\Context\Context', null);
    }

    public function testInterfaces()
    {
        $this->assertInstanceOf('FOS\RestBundle\Context\ContextInterface', $this->context);
        $this->assertInstanceOf('FOS\RestBundle\Context\GroupableContextInterface', $this->context);
        $this->assertInstanceOf('FOS\RestBundle\Context\VersionableContextInterface', $this->context);
        $this->assertInstanceOf('FOS\RestBundle\Context\MaxDepthContextInterface', $this->context);
        $this->assertInstanceOf('FOS\RestBundle\Context\SerializeNullContextInterface', $this->context);
    }

    public function testDefaultValues()
    {
        $this->assertEquals([], $this->context->getAttributes());
        $this->assertEquals([], $this->context->getGroups());
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

    public function testGroupsAddition()
    {
        $context = $this->getMock('FOS\RestBundle\Context\Context', ['addGroup']);
        $context
            ->expects($this->exactly(2))
            ->method('addGroup')
            ->withConsecutive(
                ['Default'],
                ['foo']
            );
        $this->assertEquals($context, $context->addGroups(['Default', 'foo']));
    }

    public function testGroupAddition()
    {
        $this->context->addGroup('foo');
        $this->context->addGroup('bar');

        $this->assertEquals(['foo', 'bar'], $this->context->getGroups());
    }

    public function testAlreadyExistentGroupAddition()
    {
        $this->context->addGroup('foo');
        $this->context->addGroup('foo');
        $this->context->addGroup('bar');

        $this->assertEquals(['foo', 'bar'], $this->context->getGroups());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidGroup()
    {
        $this->context->addGroup(new \stdClass());
    }

    public function testVersion()
    {
        $this->context->setVersion('1.3.2');

        $this->assertEquals('1.3.2', $this->context->getVersion());
    }

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
}
