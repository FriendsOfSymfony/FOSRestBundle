<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Request;

use Symfony\Component\HttpFoundation\Request;

/**
 * @author Tyler Stroud <tyler@tylerstroud.com>
 */
abstract class AbstractRequestBodyParamConverterTest extends \PHPUnit_Framework_TestCase
{
    protected $serializer;
    protected $converterBuilder;

    public function setUp()
    {
        $this->serializer = $this->getMock('SerializerInterface', ['deserialize']);
        $this->converterBuilder = $this->getConverterBuilder()
             ->setMethods(null)
             ->setConstructorArgs([$this->serializer]);
    }

    public function testInterface()
    {
        $abstractConverter = $this->getMockForAbstractClass('FOS\RestBundle\Request\AbstractRequestBodyParamConverter', [$this->serializer]);
        $this->assertInstanceOf('Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface', $abstractConverter);
        $converter = $this->converterBuilder->getMock();
        $this->assertInstanceOf('FOS\RestBundle\Request\AbstractRequestBodyParamConverter', $converter);
    }

    public function testSerializerSetting()
    {
        $converter = $this->converterBuilder->getMock();
        $serializerProperty = new \ReflectionProperty($converter, 'serializer');
        $serializerProperty->setAccessible(true);

        $this->assertEquals($this->serializer, $serializerProperty->getValue($converter));
    }

    public function testContextSetting()
    {
        $converter = $this->converterBuilder
            ->setConstructorArgs([$this->serializer, 'foo', 'v1'])
            ->getMock();
        $contextProperty = new \ReflectionProperty($converter, 'context');
        $contextProperty->setAccessible(true);

        $this->assertEquals(['groups' => ['foo'], 'version' => 'v1'], $contextProperty->getValue($converter));
    }

    public function testDeserializationAdapterSetting()
    {
        $adapter = $this->getMock('FOS\RestBundle\Context\Adapter\DeserializationContextAdapterInterface');
        $converter = $this->converterBuilder->getMock();
        $converter->setDeserializationContextAdapter($adapter);

        $adapterProperty = new \ReflectionProperty($converter, 'contextAdapter');
        $adapterProperty->setAccessible(true);
        $this->assertEquals($adapter, $adapterProperty->getValue($converter));
    }

    public function testDeserializationContextGetting()
    {
        $converter = $this->converterBuilder->getMock();

        $getterMethod = new \ReflectionMethod($converter, 'getDeserializationContext');
        $getterMethod->setAccessible(true);
        $this->assertInstanceOf('FOS\RestBundle\Context\ContextInterface', $getterMethod->invoke($converter, $this->createRequest()));
    }

    public function testContextMergeDuringExecution()
    {
        $options = [
            'deserializationContext' => [
                'groups' => ['foo', 'bar'],
                'foobar' => 'foo',
            ],
        ];
        $configuration = $this->createConfiguration(null, null, $options);
        $converter = $this->converterBuilder
             ->setConstructorArgs([$this->serializer, 'foogroup', 'fooversion'])
             ->setMethods(['configureDeserializationContext'])
             ->getMock();
        $converter->setDeserializationContextAdapter($this->getMock('FOS\RestBundle\Context\Adapter\DeserializationContextAdapterInterface'));
        $converter
            ->expects($this->once())
            ->method('configureDeserializationContext')
            ->with($this->anything(), ['groups' => ['foo', 'bar'], 'foobar' => 'foo', 'version' => 'fooversion'])
            ->willReturn($this->getMock('FOS\RestBundle\Context\ContextInterface'));
        $this->launchExecution($converter, null, $configuration);
    }

    public function testSerializerTransmissionToTheContextAdapter()
    {
        $adapter = $this->getMock('FOS\RestBundle\Tests\Fixtures\Context\Adapter\SerializerAwareAdapter');
        $adapter
            ->expects($this->once())
            ->method('setSerializer');
        $adapter
            ->expects($this->once())
            ->method('convertDeserializationContext');
        $converter = $this->converterBuilder->getMock();
        $converter->setDeserializationContextAdapter($adapter);
        $this->launchExecution($converter);
    }

    public function testSerializerParameters()
    {
        $converter = $this->converterBuilder->getMock();
        $converter->setDeserializationContextAdapter($this->getMock('FOS\RestBundle\Context\Adapter\DeserializationContextAdapterInterface'));
        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with('body', 'FooClass', 'json');
        $this->launchExecution($converter);
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException
     */
    public function testExecutionInterceptsUnsupportedFormatException()
    {
        $converter = $this->converterBuilder->getMock();
        $converter->setDeserializationContextAdapter($this->getMock('FOS\RestBundle\Context\Adapter\DeserializationContextAdapterInterface'));
        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->will($this->throwException(new \Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException()));
        $this->launchExecution($converter);
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function testExecutionInterceptsJMSException()
    {
        $converter = $this->converterBuilder->getMock();
        $converter->setDeserializationContextAdapter($this->getMock('FOS\RestBundle\Context\Adapter\DeserializationContextAdapterInterface'));
        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->will($this->throwException(new \JMS\Serializer\Exception\InvalidArgumentException()));
        $this->launchExecution($converter);
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function testExecutionInterceptsSymfonySerializerException()
    {
        $converter = $this->converterBuilder->getMock();
        $converter->setDeserializationContextAdapter($this->getMock('FOS\RestBundle\Context\Adapter\DeserializationContextAdapterInterface'));
        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->will($this->throwException(new \Symfony\Component\Serializer\Exception\InvalidArgumentException()));
        $this->launchExecution($converter);
    }

    public function testRequestAttribute()
    {
        $converter = $this->converterBuilder->getMock();
        $converter->setDeserializationContextAdapter($this->getMock('FOS\RestBundle\Context\Adapter\DeserializationContextAdapterInterface'));
        $this->serializer
             ->expects($this->once())
             ->method('deserialize')
             ->willReturn('Object');
        $request = $this->createRequest();
        $this->launchExecution($converter, $request);
        $this->assertEquals('Object', $request->attributes->get('foo'));
    }

    public function testValidatorParameters()
    {
        if (!interface_exists('Symfony\Component\Validator\Validator\ValidatorInterface')) {
            $this->markTestSkipped(
                'skipping testValidatorParameters due to an incompatible version of the Symfony validator component'
            );
        }
        $this->serializer
             ->expects($this->once())
             ->method('deserialize')
             ->willReturn('Object');
        $validator = $this->getMock('Symfony\Component\Validator\Validator\ValidatorInterface');
        $validator
            ->expects($this->once())
            ->method('validate')
            ->with('Object', null, ['foo'])
            ->willReturn('fooError');
        $converter = $this->converterBuilder
            ->setConstructorArgs([$this->serializer, null, null, $validator, 'errors'])
            ->getMock();
        $converter->setDeserializationContextAdapter($this->getMock('FOS\RestBundle\Context\Adapter\DeserializationContextAdapterInterface'));
        $request = $this->createRequest();
        $configuration = $this->createConfiguration(null, null, ['validator' => ['groups' => ['foo']]]);
        $this->launchExecution($converter, $request, $configuration);
        $this->assertEquals('fooError', $request->attributes->get('errors'));
    }

    public function testReturn()
    {
        $converter = $this->converterBuilder->getMock();
        $converter->setDeserializationContextAdapter($this->getMock('FOS\RestBundle\Context\Adapter\DeserializationContextAdapterInterface'));
        $this->assertTrue($this->launchExecution($converter));
    }

    public function testContextConfiguration()
    {
        $converter = $this->converterBuilder->getMock();
        $options = [
            'groups' => ['foo', 'bar'],
            'version' => 'v1.2',
            'maxDepth' => 5,
            'serializeNull' => false,
            'foo' => 'bar',
        ];
        $context = $this->getMock('FOS\RestBundle\Context\Context');
        $context
            ->expects($this->once())
            ->method('addGroups')
            ->with($options['groups']);
        $context
            ->expects($this->once())
            ->method('setVersion')
            ->with($options['version']);
        $context
            ->expects($this->once())
            ->method('setMaxDepth')
            ->with($options['maxDepth']);
        $context
            ->expects($this->once())
            ->method('setSerializeNull')
            ->with($options['serializeNull']);

        $contextConfigurationMethod = new \ReflectionMethod($converter, 'configureDeserializationContext');
        $contextConfigurationMethod->setAccessible(true);
        $contextConfigurationMethod->invoke($converter, $context, $options);
    }

    public function testValidatorOptionsGetter()
    {
        $converter = $this->converterBuilder->getMock();

        $options1 = [
            'validator' => [
                'groups' => ['foo'],
                'traverse' => true,
            ],
        ];
        $options2 = [
            'validator' => [
                'deep' => true,
            ],
        ];

        $validatorMethod = new \ReflectionMethod($converter, 'getValidatorOptions');
        $validatorMethod->setAccessible(true);
        $this->assertEquals(['groups' => ['foo'], 'traverse' => true, 'deep' => false], $validatorMethod->invoke($converter, $options1));
        $this->assertEquals(['groups' => false, 'traverse' => false, 'deep' => true], $validatorMethod->invoke($converter, $options2));
    }

    public function testApply()
    {
        $request = $this->createRequest();
        $configuration = $this->createConfiguration('FooClass', 'foo');
        $converter = $this->converterBuilder
            ->setConstructorArgs([$this->serializer])
            ->setMethods(['execute'])
            ->getMock();
        $converter
            ->expects($this->once())
            ->method('execute')
            ->with($request, $configuration)
            ->willReturn(true);

        $this->assertTrue($converter->apply($request, $configuration));
    }

    public function testSupports()
    {
        $converter = $this->converterBuilder->getMock();
        $config = $this->createConfiguration('FOS\RestBundle\Tests\Request\Post', 'post');
        $this->assertTrue($converter->supports($config));
    }

    public function testSupportsWithNoClass()
    {
        $converter = $this->converterBuilder->getMock();
        $this->assertFalse($converter->supports($this->createConfiguration(null, 'post')));
    }

    protected function launchExecution($converter, $request = null, $configuration = null)
    {
        if ($request === null) {
            $request = $this->createRequest('body', 'application/json');
        }
        if ($configuration === null) {
            $configuration = $this->createConfiguration('FooClass', 'foo');
        }

        $executionMethod = new \ReflectionMethod($converter, 'execute');
        $executionMethod->setAccessible(true);

        return $executionMethod->invoke($converter, $request, $configuration);
    }

    protected function createConfiguration($class = null, $name = null, $options = null)
    {
        $config = $this->getMockBuilder('Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter')
            ->disableOriginalConstructor()
            ->setMethods(['getClass', 'getAliasName', 'getOptions', 'getName', 'allowArray'])
            ->getMock();

        if ($name !== null) {
            $config->expects($this->any())
                ->method('getName')
                ->will($this->returnValue($name));
        }

        if ($class !== null) {
            $config->expects($this->any())
                ->method('getClass')
                ->will($this->returnValue($class));
        }

        if ($options !== null) {
            $config->expects($this->any())
                ->method('getOptions')
                ->will($this->returnValue($options));
        }

        return $config;
    }

    protected function createRequest($body = null, $contentType = null)
    {
        $request = new Request(
            [],
            [],
            [],
            [],
            [],
            [],
            $body
        );
        $request->headers->set('CONTENT_TYPE', $contentType);

        return $request;
    }

    abstract protected function getConverterBuilder();
}
