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

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Request\RequestBodyParamConverter;
use PHPUnit\Framework\TestCase;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Tyler Stroud <tyler@tylerstroud.com>
 */
class RequestBodyParamConverterTest extends TestCase
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

        $this->serializer = $this->getMockBuilder('FOS\RestBundle\Serializer\Serializer')->getMock();
        $this->converter = new RequestBodyParamConverter($this->serializer);
    }

    public function testInterface()
    {
        $converter = new RequestBodyParamConverter($this->serializer);
        $this->assertInstanceOf('Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface', $converter);
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
        $converter = $this->getMockBuilder(RequestBodyParamConverter::class)
             ->setConstructorArgs([$this->serializer, ['foogroup'], 'fooversion'])
             ->setMethods(['configureContext'])
             ->getMock();
        $converter
            ->expects($this->once())
            ->method('configureContext')
            ->with($this->anything(), ['groups' => ['foo', 'bar'], 'foobar' => 'foo', 'version' => 'fooversion'])
            ->willReturn(new Context());
        $this->launchExecution($converter, null, $configuration);
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException
     */
    public function testExecutionInterceptsUnsupportedFormatException()
    {
        $converter = new RequestBodyParamConverter($this->serializer);
        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->will($this->throwException(new \Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException()));
        $this->launchExecution($converter);
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function testExecutionInterceptsJMSException()
    {
        if (!class_exists('JMS\SerializerBundle\JMSSerializerBundle')) {
            $this->markTestSkipped('JMSSerializerBundle is not installed.');
        }

        $converter = new RequestBodyParamConverter($this->serializer);
        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->will($this->throwException(new \JMS\Serializer\Exception\InvalidArgumentException()));
        $this->launchExecution($converter);
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function testExecutionInterceptsSymfonySerializerException()
    {
        $converter = new RequestBodyParamConverter($this->serializer);
        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->will($this->throwException(new \Symfony\Component\Serializer\Exception\InvalidArgumentException()));
        $this->launchExecution($converter);
    }

    public function testRequestAttribute()
    {
        $converter = new RequestBodyParamConverter($this->serializer);
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
        $this->serializer
             ->expects($this->once())
             ->method('deserialize')
             ->willReturn('Object');

        $validator = $this->getMockBuilder('Symfony\Component\Validator\Validator\ValidatorInterface')->getMock();
        $validator
            ->expects($this->once())
            ->method('validate')
            ->with('Object', null, ['foo'])
            ->willReturn('fooError');

        $converter = new RequestBodyParamConverter($this->serializer, null, null, $validator, 'errors');

        $request = $this->createRequest();
        $configuration = $this->createConfiguration(null, null, ['validator' => ['groups' => ['foo']]]);
        $this->launchExecution($converter, $request, $configuration);
        $this->assertEquals('fooError', $request->attributes->get('errors'));
    }

    public function testValidatorSkipping()
    {
        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->willReturn('Object');

        $validator = $this->getMockBuilder('Symfony\Component\Validator\Validator\ValidatorInterface')->getMock();
        $validator
            ->expects($this->never())
            ->method('validate');

        $converter = new RequestBodyParamConverter($this->serializer, null, null, $validator, 'errors');

        $request = $this->createRequest();
        $configuration = $this->createConfiguration(null, null, ['validate' => false]);
        $this->launchExecution($converter, $request, $configuration);
        $this->assertNull($request->attributes->get('errors'));
    }

    public function testReturn()
    {
        $converter = new RequestBodyParamConverter($this->serializer);
        $this->assertTrue($this->launchExecution($converter));
    }

    public function testContextConfiguration()
    {
        $converter = new RequestBodyParamConverter($this->serializer);
        $options = [
            'groups' => ['foo', 'bar'],
            'version' => 'v1.2',
            'enableMaxDepth' => true,
            'serializeNull' => false,
            'foo' => 'bar',
        ];

        $contextConfigurationMethod = new \ReflectionMethod($converter, 'configureContext');
        $contextConfigurationMethod->setAccessible(true);
        $contextConfigurationMethod->invoke($converter, $context = new Context(), $options);

        $expectedContext = new Context();
        $expectedContext
            ->addGroups($options['groups'])
            ->setVersion($options['version'])
            ->enableMaxDepth($options['enableMaxDepth'])
            ->setSerializeNull($options['serializeNull'])
            ->setAttribute('foo', 'bar');

        $this->assertEquals($expectedContext, $context);
    }

    /**
     * @group legacy
     */
    public function testMaxDepthContextConfiguration()
    {
        $converter = new RequestBodyParamConverter($this->serializer);
        $options = [
            'maxDepth' => 5,
        ];

        $contextConfigurationMethod = new \ReflectionMethod($converter, 'configureContext');
        $contextConfigurationMethod->setAccessible(true);
        $contextConfigurationMethod->invoke($converter, $context = new Context(), $options);

        $expectedContext = new Context();
        $expectedContext
            ->setMaxDepth($options['maxDepth']);

        $this->assertEquals($expectedContext, $context);
    }

    public function testValidatorOptionsGetter()
    {
        $converter = new RequestBodyParamConverter($this->serializer);

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
        $converter = new RequestBodyParamConverter($this->serializer);
        $config = $this->createConfiguration('FOS\RestBundle\Tests\Request\Post', 'post');
        $this->assertTrue($converter->supports($config));
    }

    public function testSupportsWithNoClass()
    {
        $converter = new RequestBodyParamConverter($this->serializer);
        $this->assertFalse($converter->supports($this->createConfiguration(null, 'post')));
    }

    protected function launchExecution($converter, $request = null, $configuration = null)
    {
        if (null === $request) {
            $request = $this->createRequest('body', 'application/json');
        }
        if (null === $configuration) {
            $configuration = $this->createConfiguration('FooClass', 'foo');
        }

        return $converter->apply($request, $configuration);
    }

    protected function createConfiguration($class = null, $name = null, array $options = array())
    {
        return new ParamConverter([
            'name' => $name,
            'class' => $class,
            'options' => $options,
            'converter' => 'fos_rest.request_body',
        ]);
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
}
