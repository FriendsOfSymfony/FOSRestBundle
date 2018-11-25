<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Serializer;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Serializer\JMSSerializerAdapter;
use JMS\Serializer\ContextFactory\DeserializationContextFactoryInterface;
use JMS\Serializer\ContextFactory\SerializationContextFactoryInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exclusion\ExclusionStrategyInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\TestCase;

class JMSSerializerAdapterTest extends TestCase
{
    private $serializer;
    private $serializationContextFactory;
    private $deserializationContextFactory;
    private $adapter;

    protected function setUp()
    {
        if (!class_exists('JMS\SerializerBundle\JMSSerializerBundle')) {
            $this->markTestSkipped('JMSSerializerBundle is not installed.');
        }

        $this->serializer = $this->getMockBuilder(SerializerInterface::class)->getMock();

        $this->serializationContextFactory = $this
            ->getMockBuilder(SerializationContextFactoryInterface::class)->getMock();
        $this->deserializationContextFactory = $this
            ->getMockBuilder(DeserializationContextFactoryInterface::class)->getMock();

        $this->adapter = new JMSSerializerAdapter(
            $this->serializer,
            $this->serializationContextFactory,
            $this->deserializationContextFactory
        );
    }

    public function testBasicSerializeAdapterWithoutContextFactories()
    {
        $jmsContext = SerializationContext::create();
        $adapter = new JMSSerializerAdapter($this->serializer);
        $this->serializer->expects($this->once())->method('serialize')->with('foo', 'json', $jmsContext);

        $adapter->serialize('foo', 'json', new Context());
    }

    public function testBasicDeSerializeAdapterWithoutContextFactories()
    {
        $jmsContext = DeserializationContext::create();
        $adapter = new JMSSerializerAdapter($this->serializer);
        $this->serializer->expects($this->once())->method('deserialize')->with('foo', 'string', 'json', $jmsContext);

        $adapter->deserialize('foo', 'string', 'json', new Context());
    }

    public function testBasicSerializeAdapter()
    {
        $jmsContext = $this->getMockBuilder(SerializationContext::class)->getMock();

        $this->serializer->expects($this->once())->method('serialize')->with('foo', 'json', $jmsContext);
        $this->serializationContextFactory->expects($this->once())->method('createSerializationContext')
            ->willReturn($jmsContext);

        $this->adapter->serialize('foo', 'json', new Context());
    }

    public function testBasicDeserializeAdapter()
    {
        $jmsContext = $this->getMockBuilder(DeserializationContext::class)->getMock();

        $this->serializer->expects($this->once())->method('deserialize')->with('foo', 'string', 'json', $jmsContext);
        $this->deserializationContextFactory->expects($this->once())->method('createDeserializationContext')
            ->willReturn($jmsContext);

        $this->adapter->deserialize('foo', 'string', 'json', new Context());
    }

    public function testContextInfoAreConverted()
    {
        $exclusion = $this->getMockBuilder(ExclusionStrategyInterface::class)->getMock();

        $jmsContext = $this->getMockBuilder(SerializationContext::class)->getMock();

        $jmsContext->expects($this->once())->method('setGroups')->with(['foo']);
        $jmsContext->expects($this->once())->method('setSerializeNull')->with(true);
        $jmsContext->expects($this->once())->method('enableMaxDepthChecks');
        $jmsContext->expects($this->once())->method('setVersion')->with('5.0.1');
        $jmsContext->expects($this->once())->method('addExclusionStrategy')->with($exclusion);
        $jmsContext->expects($this->once())->method('setAttribute')->with('foo', 'bar');

        $this->serializationContextFactory->method('createSerializationContext')->willReturn($jmsContext);

        $fosContext = new Context();
        $fosContext->setAttribute('foo', 'bar');
        $fosContext->setGroups(['foo']);
        $fosContext->setSerializeNull(true);
        $fosContext->setVersion('5.0.1');
        $fosContext->enableMaxDepth();
        $fosContext->addExclusionStrategy($exclusion);

        $this->adapter->serialize('foo', 'json', $fosContext);
    }
}
