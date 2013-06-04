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

use FOS\RestBundle\Request\RequestBodyParamConverter;
use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\Exception\UnsupportedFormatException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Tyler Stroud <tyler@tylerstroud.com>
 */
class RequestBodyParamConverterTest extends \PHPUnit_Framework_TestCase
{
    protected $serializer;
    protected $converter;

    public function setUp()
    {
        $this->serializer = $this->getMock('JMS\Serializer\SerializerInterface');
        $this->converter = $this->getMock(
            'FOS\RestBundle\Request\RequestBodyParamConverter',
            array('getDeserializationContext'),
            array($this->serializer)
        );
    }

    public function testSupports()
    {
        $config = $this->createConfiguration('FOS\RestBundle\Tests\Request\Post', 'post');
        $this->assertTrue($this->converter->supports($config));
    }

    public function testSupportsWithNoClass()
    {
        $this->assertFalse($this->converter->supports($this->createConfiguration(null, 'post')));
    }

    public function testApply()
    {
        $requestBody = '{"name": "Post 1", "body": "This is a blog post"}';
        $expectedPost = new Post('Post 1', 'This is a blog post');

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with($requestBody, 'FOS\RestBundle\Tests\Request\Post', 'json')
            ->will($this->returnValue($expectedPost));

        $this->converter->expects($this->once())
            ->method('getDeserializationContext')
            ->will($this->returnValue($this->createDeserializationContext()));

        $request = $this->createRequest('{"name": "Post 1", "body": "This is a blog post"}', 'application/json');

        $config = $this->createConfiguration('FOS\RestBundle\Tests\Request\Post', 'post');
        $this->converter->apply($request, $config);

        $this->assertSame($expectedPost, $request->attributes->get('post'));
    }

    public function testApplyWithUnsupportedContentType()
    {
        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->will($this->throwException(new UnsupportedFormatException('unsupported format')));

        $this->converter->expects($this->once())
            ->method('getDeserializationContext')
            ->will($this->returnValue($this->createDeserializationContext()));

        $request = $this->createRequest('', 'text/html');

        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\HttpException', 'unsupported format');

        $config = $this->createConfiguration('FOS\RestBundle\Tests\Request\Post', 'post');
        $this->converter->apply($request, $config);
    }

    public function testApplyWhenSerializerThrowsException()
    {
        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->will($this->throwException(new RuntimeException('serializer exception')));

        $this->converter->expects($this->once())
            ->method('getDeserializationContext')
            ->will($this->returnValue($this->createDeserializationContext()));

        $request = $this->createRequest();

        $this->setExpectedException(
            'Symfony\Component\HttpKernel\Exception\HttpException',
            'serializer exception'
        );

        $config = $this->createConfiguration('FOS\RestBundle\Tests\Request\Post', 'post');
        $this->converter->apply($request, $config);
    }

    public function testApplyWithSerializerContextOptionsForJMSSerializer()
    {
        $requestBody = '{"name": "Post 1", "body": "This is a blog post"}';
        $options = array(
            'deserializationContext' => array(
                'groups' => array('group1'),
                'version' => '1.0'
            )
        );

        $context = $this->createDeserializationContext(
            $options['deserializationContext']['groups'],
            $options['deserializationContext']['version']
        );

        $this->converter->expects($this->once())
            ->method('getDeserializationContext')
            ->will($this->returnValue($context));

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with($requestBody, 'FOS\RestBundle\Tests\Request\Post', 'json', $context);

        $request = $this->createRequest($requestBody, 'application/json');
        $config = $this->createConfiguration('FOS\RestBundle\Tests\Request\Post', 'post', $options);

        $this->converter->apply($request, $config);
    }

    public function testApplyWithDefaultSerializerContextExclusionPolicy()
    {
        $this->converter = $this->getMock(
            'FOS\RestBundle\Request\RequestBodyParamConverter',
            array('getDeserializationContext'),
            array($this->serializer, array('group1'), '1.0')
        );

        $context = $this->createDeserializationContext(array('group1'), '1.0');
        $request = $this->createRequest('', 'application/json');
        $config = $this->createConfiguration('FOS\RestBundle\Tests\Request\Post', 'post');

        $this->converter->expects($this->once())
            ->method('getDeserializationContext')
            ->will($this->returnValue($context));

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with('', 'FOS\RestBundle\Tests\Request\Post', 'json', $context);

        $this->converter->apply($request, $config);
    }

    public function testApplyWithSerializerContextOptionsForSymfonySerializer()
    {
        $this->serializer = $this->getMock('Symfony\Component\Serializer\SerializerInterface', array('deserialize'));
        $this->converter = new RequestBodyParamConverter($this->serializer);
        $requestBody = '{"name": "Post 1", "body": "This is a blog post"}';

        $options = array(
            'deserializationContext' => array(
                'json_decode_options' => 2, // JSON_BIGINT_AS_STRING
            )
        );

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with($requestBody, 'FOS\RestBundle\Tests\Request\Post', 'json', $options['deserializationContext']);

        $request = $this->createRequest($requestBody, 'application/json');
        $config = $this->createConfiguration('FOS\RestBundle\Tests\Request\Post', 'post', $options);

        $this->converter->apply($request, $config);
    }

    protected function createConfiguration($class = null, $name = null, array $options = null)
    {
        $config = $this->getMock(
            'Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface',
            array('getClass', 'getAliasName', 'getOptions', 'getName', 'allowArray')
        );

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
            array(),
            array(),
            array(),
            array(),
            array(),
            array(),
            $body
        );
        $request->headers->set('CONTENT_TYPE', $contentType);

        return $request;
    }

    protected function createDeserializationContext($groups = null, $version = null)
    {
        $context = $this->getMock('JMS\Serializer\DeserializationContext');
        if (null !== $groups) {
            $context->expects($this->once())
                ->method('setGroups')
                ->with($groups);
        }
        if (null !== $version) {
            $context->expects($this->once())
                ->method('setVersion')
                ->with($version);
        }

        return $context;
    }
}

class Post
{
    public $name;
    public $body;

    public function __construct($name, $body)
    {
        $this->name = $name;
        $this->body = $body;
    }
}
