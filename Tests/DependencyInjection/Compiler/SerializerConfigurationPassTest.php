<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\DependencyInjection\Compiler;

use FOS\RestBundle\DependencyInjection\Compiler\SerializerConfigurationPass;
use FOS\RestBundle\Serializer\Serializer;
use JMS\Serializer\Serializer as JmsSerializer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Serializer\Serializer as SymfonySerializer;

/**
 * SerializerConfigurationPassTest test.
 */
class SerializerConfigurationPassTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();
    }

    public function testShouldDoNothingIfSerializerIsFound(): void
    {
        $serializer = $this->getMockBuilder(Serializer::class)->getMock();
        $this->container->register('fos_rest.serializer', get_class($serializer));

        $compiler = new SerializerConfigurationPass();
        $compiler->process($this->container);

        $this->assertSame(get_class($serializer), $this->container->getDefinition('fos_rest.serializer')->getClass());
    }

    public function testShouldThrowInvalidArgumentExceptionWhenNoSerializerIsFound(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $compiler = new SerializerConfigurationPass();
        $compiler->process($this->container);
    }

    public function testShouldConfigureJMSSerializer(): void
    {
        $this->container->register('jms_serializer.serializer', \JMS\Serializer\Serializer::class);

        $compiler = new SerializerConfigurationPass();
        $compiler->process($this->container);

        $this->assertSame('fos_rest.serializer.jms', (string) $this->container->getAlias('fos_rest.serializer'));
    }

    public function testShouldConfigureCoreSerializer(): void
    {
        $this->container->register('serializer', SymfonySerializer::class);

        $compiler = new SerializerConfigurationPass();
        $compiler->process($this->container);

        $this->assertSame('fos_rest.serializer.symfony', (string) $this->container->getAlias('fos_rest.serializer'));
    }

    public function testJmsSerializerServiceSupersedesSerializerService(): void
    {
        $this->container->register('jms_serializer.serializer', JmsSerializer::class);
        $this->container->register('serializer', SymfonySerializer::class);

        $compiler = new SerializerConfigurationPass();
        $compiler->process($this->container);

        $this->assertSame('fos_rest.serializer.jms', (string) $this->container->getAlias('fos_rest.serializer'));
    }

    public function testSerializerServiceCanBeJmsSerializer(): void
    {
        $this->container->register('jms_serializer.serializer', \JMS\Serializer\Serializer::class);
        $this->container->register('serializer', \JMS\Serializer\Serializer::class);

        $compiler = new SerializerConfigurationPass();
        $compiler->process($this->container);

        $this->assertSame('fos_rest.serializer.jms', (string) $this->container->getAlias('fos_rest.serializer'));
    }
}
