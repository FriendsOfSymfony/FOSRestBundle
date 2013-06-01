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
        $this->serializer = $this->getMock('JMS\Serializer\Serializer', array(), array(), '', false);
        $this->converter = new RequestBodyParamConverter($this->serializer);
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

        $request = $this->createRequest();

        $this->setExpectedException(
            'Symfony\Component\HttpKernel\Exception\HttpException',
            'serializer exception'
        );

        $config = $this->createConfiguration('FOS\RestBundle\Tests\Request\Post', 'post');
        $this->converter->apply($request, $config);
    }

    protected function createConfiguration($class = null, $name = null)
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
