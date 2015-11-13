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
class RequestBodyParamConverterTest extends \PHPUnit_Framework_TestCase
{
    protected $serializer;
    protected $converterBuilder;

    public function setUp()
    {
        // skip the test if the installed version of SensioFrameworkExtraBundle
        // is not compatible with the RequestBodyParamConverter class
        $parameter = new \ReflectionParameter(
            [
                'Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface',
                'supports',
            ],
            'configuration'
        );
        if ('Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter' != $parameter->getClass()->getName()) {
            $this->markTestSkipped(
                'skipping RequestBodyParamConverterTest due to an incompatible version of the SensioFrameworkExtraBundle'
            );
        }

        $this->serializer = $this->getMock('SerializerInterface', ['deserialize']);
        $this->converterBuilder = $this->getConverterBuilder()
             ->setMethods(null)
             ->setConstructorArgs([$this->serializer]);
    }

    public function testInterface()
    {
        $converter = $this->converterBuilder->getMock();
        $this->assertInstanceOf('Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface', $converter);
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

        return $converter->apply($request, $configuration);
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

    protected function getConverterBuilder()
    {
        return $this->getMockBuilder('FOS\RestBundle\Request\RequestBodyParamConverter');
    }
}
