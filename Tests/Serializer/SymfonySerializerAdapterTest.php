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
use FOS\RestBundle\Serializer\SymfonySerializerAdapter;
use FOS\RestBundle\Serializer\Serializer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;

class SymfonySerializerAdapterTest extends TestCase
{
    private $serializer;
    private $adapter;

    protected function setUp(): void
    {
        $this->serializer = $this->getMockBuilder(SerializerInterface::class)->getMock();

        $this->adapter = new SymfonySerializerAdapter($this->serializer);
    }

    public function testHasFosRestContextAttributeByDefault()
    {
        $context = new Context();
        $this->serializer
            ->expects($this->once())
            ->method('serialize')
            ->willReturnCallback(function ($data, $format, $context) {
                self::assertSame(1, $data);
                self::assertSame('json', $format);
                self::assertSame(true, $context[Serializer::FOS_BUNDLE_SERIALIZATION_CONTEXT]);

                return 'abc';
            });

        $this->adapter->serialize(1, 'json', $context);
    }

    public function testCanOverrideContextAttribute()
    {
        $context = new Context();
        $context->setAttribute(Serializer::FOS_BUNDLE_SERIALIZATION_CONTEXT, false);
        $this->serializer
            ->expects($this->once())
            ->method('serialize')
            ->willReturnCallback(function ($data, $format, $context) {
                self::assertSame(1, $data);
                self::assertSame('json', $format);
                self::assertSame(false, $context[Serializer::FOS_BUNDLE_SERIALIZATION_CONTEXT]);

                return 'abc';
            });

        $this->adapter->serialize(1, 'json', $context);
    }
}
