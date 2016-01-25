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
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Request\RequestBodyParamConverter;

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

        $this->serializer = $this->getMock('FOS\RestBundle\Serializer\Serializer');
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
     * @expectedException Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException
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
     * @expectedException Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function testExecutionInterceptsJMSException()
    {
        $converter = new RequestBodyParamConverter($this->serializer);
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

        $validator = $this->getMock('Symfony\Component\Validator\Validator\ValidatorInterface');
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
            'maxDepth' => 5,
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
            ->setMaxDepth($options['maxDepth'])
            ->setSerializeNull($options['serializeNull'])
            ->setAttribute('foo', 'bar');

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
}
