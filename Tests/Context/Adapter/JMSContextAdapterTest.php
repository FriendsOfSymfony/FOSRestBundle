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

use FOS\RestBundle\Context\Adapter\JMSContextAdapter;
use FOS\RestBundle\Context\Context;

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
class JMSContextAdapterTest extends \PHPUnit_Framework_TestCase
{
    private $serializer;
    private $adapter;

    public function setUp()
    {
        $this->serializer = $this->getMock('JMS\Serializer\SerializerInterface');
        $this->adapter = new JMSContextAdapter();
        $this->adapter->setSerializer($this->serializer);
    }

    public function testInterface()
    {
        $this->assertInstanceOf('FOS\RestBundle\Context\Adapter\SerializationContextAdapterInterface', $this->adapter);
        $this->assertInstanceOf('FOS\RestBundle\Context\Adapter\DeserializationContextAdapterInterface', $this->adapter);
        $this->assertInstanceOf('FOS\RestBundle\Context\Adapter\SerializerAwareInterface', $this->adapter);
    }

    public function testSerializationContextConversion()
    {
        $context = new Context();
        $context->setAttribute('groups', ['d']);
        $context->setAttribute('foo', 'bar');
        $context->setAttribute('version', 1);
        $context->addGroups(['a', 'b', 'c']);
        $context->setVersion(1.3);
        $context->setMaxDepth(10);
        $context->setSerializeNull(true);

        $JMSContext = $this->adapter->convertSerializationContext($context);
        $this->assertInstanceOf('JMS\Serializer\SerializationContext', $JMSContext);
        $this->assertEquals('bar', $JMSContext->attributes->get('foo')->get());
        $this->assertEquals(['a', 'b', 'c'], $JMSContext->attributes->get('groups')->get());
        $this->assertEquals(1.3, $JMSContext->attributes->get('version')->get());
        $this->assertEquals(true, $JMSContext->shouldSerializeNull());
    }

    public function testDeserializationContextConversion()
    {
        $context = new Context();
        $context->setAttribute('bar', 'foo');
        $context->setAttribute('version', 1);
        $context->addGroups(['e', 'f']);
        $context->setVersion(1.4);
        $context->setMaxDepth(10);
        $context->setSerializeNull(false);

        $JMSContext = $this->adapter->convertDeserializationContext($context);
        $this->assertInstanceOf('JMS\Serializer\DeserializationContext', $JMSContext);
        $this->assertEquals('foo', $JMSContext->attributes->get('bar')->get());
        $this->assertEquals(['e', 'f'], $JMSContext->attributes->get('groups')->get());
        $this->assertEquals(1.4, $JMSContext->attributes->get('version')->get());
        $this->assertEquals(false, $JMSContext->shouldSerializeNull());
    }
}
