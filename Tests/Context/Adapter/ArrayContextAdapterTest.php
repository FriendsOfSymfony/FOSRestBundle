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

use FOS\RestBundle\Context\Adapter\ArrayContextAdapter;
use FOS\RestBundle\Context\Context;

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
class ArrayContextAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->adapter = new ArrayContextAdapter();
        $this->serializer = $this->getMock('Symfony\Component\Serializer\SerializerInterface');
    }

    public function testInterface()
    {
        $this->assertInstanceOf('FOS\RestBundle\Context\Adapter\SerializationContextAdapterInterface', $this->adapter);
        $this->assertInstanceOf('FOS\RestBundle\Context\Adapter\DeserializationContextAdapterInterface', $this->adapter);
    }

    public function testContextConversion()
    {
        $context = new Context();
        $context->setAttribute('groups', ['d']);
        $context->setAttribute('foo', 'bar');
        $context->setAttribute('version', 1);
        $context->addGroups(['a', 'b', 'c']);
        $context->setVersion(1.3);
        $context->setMaxDepth(10);
        $context->setSerializeNull(false);

        $serializationContext = $this->adapter->convertSerializationContext($context);
        $this->assertTrue(is_array($serializationContext));
        $this->assertEquals(['groups' => ['a', 'b', 'c'], 'foo' => 'bar', 'version' => 1.3, 'maxDepth' => 10, 'serializeNull' => false], $serializationContext);

        $deserializationContext = $this->adapter->convertDeserializationContext($context);
        $this->assertTrue(is_array($deserializationContext));
        $this->assertEquals(['groups' => ['a', 'b', 'c'], 'foo' => 'bar', 'version' => 1.3, 'maxDepth' => 10], $deserializationContext);
    }

    public function testSupports()
    {
        $context = new Context();
        $this->assertTrue($this->adapter->supportsSerialization($context));
        $this->assertTrue($this->adapter->supportsDeserialization($context));
    }
}
