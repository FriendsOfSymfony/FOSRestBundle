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
use FOS\RestBundle\Serializer\Serializer;
use FOS\RestBundle\Tests\Functional\Bundle\TestBundle\Controller\Post;
use JMS\Serializer\Exception\InvalidArgumentException as JmsInvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\Serializer\Exception\InvalidArgumentException as SymfonyInvalidArgumentException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Tyler Stroud <tyler@tylerstroud.com>
 */
class RequestBodyParamConverterTest extends TestCase
{
    protected $serializer;
    protected $converter;

    public static function setUpBeforeClass(): void
    {
        if (!class_exists(ParamConverterInterface::class)) {
            self::markTestSkipped('Test requires sensio/framework-extra-bundle');
        }
    }

    protected function setUp(): void
    {
        $this->serializer = $this->getMockBuilder(Serializer::class)->getMock();
        $this->converter = new RequestBodyParamConverter($this->serializer);
    }

    public function testInterface()
    {
        $converter = new RequestBodyParamConverter($this->serializer);
        $this->assertInstanceOf(ParamConverterInterface::class, $converter);
    }

    public function testContextMergeDuringExecution()
    {
        $options = [
            'deserializationContext' => [
                'groups' => ['foo', 'bar'],
                'foobar' => 'foo',
            ],
        ];
        $configuration = $this->createConfiguration('FooClass', null, $options);
        $converter = new RequestBodyParamConverter($this->serializer, ['foogroup'], 'fooversion');

        $request = $this->createRequest('body', 'application/json');

        $expectedContext = new Context();
        $expectedContext->setGroups(['foo', 'bar']);
        $expectedContext->setVersion('fooversion');
        $expectedContext->setAttribute('foobar', 'foo');

        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with(
                'body',
                'FooClass',
                'json',
                $expectedContext
            );

        return $converter->apply($request, $configuration);
    }

    public function testExecutionInterceptsUnsupportedFormatException()
    {
        $this->expectException(UnsupportedMediaTypeHttpException::class);

        $converter = new RequestBodyParamConverter($this->serializer);
        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->will($this->throwException(new UnsupportedMediaTypeHttpException()));
        $this->launchExecution($converter);
    }

    public function testExecutionInterceptsJMSException()
    {
        $this->expectException(BadRequestHttpException::class);

        $converter = new RequestBodyParamConverter($this->serializer);
        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->will($this->throwException(new JmsInvalidArgumentException()));
        $this->launchExecution($converter);
    }

    public function testExecutionInterceptsSymfonySerializerException()
    {
        $this->expectException(BadRequestHttpException::class);

        $converter = new RequestBodyParamConverter($this->serializer);
        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->will($this->throwException(new SymfonyInvalidArgumentException()));
        $this->launchExecution($converter);
    }

    public function testRequestAttribute()
    {
        $converter = new RequestBodyParamConverter($this->serializer);
        $this->serializer
             ->expects($this->once())
             ->method('deserialize')
             ->willReturn('Object');
        $request = $this->createRequest(null, 'application/json');
        $this->launchExecution($converter, $request);
        $this->assertEquals('Object', $request->attributes->get('foo'));
    }

    public function testValidatorParameters()
    {
        $this->serializer
             ->expects($this->once())
             ->method('deserialize')
             ->willReturn('Object');

        $errors = $this->getMockBuilder(ConstraintViolationListInterface::class)->getMock();

        $validator = $this->getMockBuilder(ValidatorInterface::class)->getMock();
        $validator
            ->expects($this->once())
            ->method('validate')
            ->with('Object', null, ['foo'])
            ->willReturn($errors);

        $converter = new RequestBodyParamConverter($this->serializer, null, null, $validator, 'errors');

        $request = $this->createRequest(null, 'application/json');
        $configuration = $this->createConfiguration('FooClass', null, ['validator' => ['groups' => ['foo']]]);
        $this->launchExecution($converter, $request, $configuration);
        $this->assertEquals($errors, $request->attributes->get('errors'));
    }

    public function testValidatorSkipping()
    {
        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->willReturn('Object');

        $validator = $this->getMockBuilder(ValidatorInterface::class)->getMock();
        $validator
            ->expects($this->never())
            ->method('validate');

        $converter = new RequestBodyParamConverter($this->serializer, null, null, $validator, 'errors');

        $request = $this->createRequest(null, 'application/json');
        $configuration = $this->createConfiguration('FooClass', null, ['validate' => false]);
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
        $config = $this->createConfiguration(Post::class, 'post');
        $this->assertTrue($converter->supports($config));
    }

    public function testSupportsWithNoClass()
    {
        $converter = new RequestBodyParamConverter($this->serializer);
        $this->assertFalse($converter->supports($this->createConfiguration(null, 'post')));
    }

    public function testNoContentTypeCausesUnsupportedMediaTypeException()
    {
        $converter = new RequestBodyParamConverter($this->serializer);
        $request = $this->createRequest();
        $this->expectException(UnsupportedMediaTypeHttpException::class);
        $this->launchExecution($converter, $request);
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

    protected function createConfiguration($class, $name = null, array $options = [])
    {
        return new ParamConverter([
            'name' => (string) $name,
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
