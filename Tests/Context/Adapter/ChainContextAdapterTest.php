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

use FOS\RestBundle\Context\Adapter\ChainContextAdapter;
use FOS\RestBundle\Context\Adapter\SerializationContextAdapterInterface;
use FOS\RestBundle\Context\Adapter\DeserializationContextAdapterInterface;
use FOS\RestBundle\Context\ContextInterface;

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
class ChainContextAdapterTest extends \PHPUnit_Framework_TestCase
{
    private $context;

    public function setUp()
    {
        $this->context = $this->getMock(ContextInterface::class);

        $this->adapter1 = $this->getMock(SerializationContextAdapterInterface::class);
        $this->adapter2 = $this->getMock(\FOS\RestBundle\Tests\Fixtures\Context\Adapter\SerializerAwareAdapter::class);

        $this->adapter = new ChainContextAdapter([$this->adapter1, $this->adapter2]);
    }

    public function testInterface()
    {
        $this->assertInstanceOf(SerializationContextAdapterInterface::class, $this->adapter);
        $this->assertInstanceOf(DeserializationContextAdapterInterface::class, $this->adapter);
    }

    public function testSerializationContextConversion()
    {
        $this->adapter1
            ->expects($this->once())
            ->method('supportsSerialization')
            ->will($this->returnValue(true));
        $this->adapter1
            ->expects($this->once())
            ->method('convertSerializationContext')
            ->will($this->returnValue('foo'));
        $newContext = $this->adapter->convertSerializationContext($this->context);
        $this->assertEquals('foo', $newContext);
    }

    public function testDeserializationContextConversion()
    {
        $this->adapter2
            ->expects($this->once())
            ->method('supportsDeserialization')
            ->with($this->context)
            ->will($this->returnValue(true));
        $this->adapter2
            ->expects($this->once())
            ->method('convertDeserializationContext')
            ->with($this->context)
            ->will($this->returnValue('foo'));
        $newContext = $this->adapter->convertDeserializationContext($this->context);
        $this->assertEquals('foo', $newContext);
    }

    /**
     * @expectedException \LogicException
     */
    public function testNotSupportedSerializationContextConversion()
    {
        $this->adapter->convertSerializationContext($this->context);
    }

    /**
     * @expectedException \LogicException
     */
    public function testNotSupportedDeserializationContextConversion()
    {
        $this->adapter->convertDeserializationContext($this->context);
    }

    public function testSerializationSupport()
    {
        $this->adapter1
            ->expects($this->once())
            ->method('supportsSerialization')
            ->with($this->context)
            ->will($this->returnValue(true));
        $this->adapter2
            ->expects($this->once())
            ->method('supportsDeserialization')
            ->with($this->context)
            ->will($this->returnValue(false));
        $this->assertTrue($this->adapter->supportsSerialization($this->context));
        $this->assertFalse($this->adapter->supportsDeserialization($this->context));
    }

    public function testDeserializationSupport()
    {
        $this->adapter1
            ->expects($this->once())
            ->method('supportsSerialization')
            ->with($this->context)
            ->will($this->returnValue(false));
        $this->adapter2
            ->expects($this->any())
            ->method('supportsDeserialization')
            ->with($this->context)
            ->will($this->returnValue(true));
        $this->assertFalse($this->adapter->supportsSerialization($this->context));
        $this->assertTrue($this->adapter->supportsDeserialization($this->context));
    }

    public function testSerializerTransmission()
    {
        $serializer = $this->getMock(\JMS\Serializer\Serializer::class, [], [], '', false);
        $this->adapter->setSerializer($serializer);
        $this->adapter2
             ->expects($this->exactly(4))
             ->method('setSerializer')
             ->with($serializer);
        $this->adapter2
             ->expects($this->exactly(2))
             ->method('supportsSerialization')
             ->will($this->returnValue(true));
        $this->adapter2
             ->expects($this->exactly(2))
             ->method('supportsDeserialization')
             ->will($this->returnValue(true));

        $this->adapter->supportsSerialization($this->context);
        $this->adapter->supportsDeserialization($this->context);
        $this->adapter->convertSerializationContext($this->context);
        $this->adapter->convertDeserializationContext($this->context);
    }
}
