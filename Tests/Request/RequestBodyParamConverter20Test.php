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

use FOS\RestBundle\Request\RequestBodyParamConverter20;
use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\Exception\UnsupportedFormatException;

class RequestBodyParamConverter20Test extends AbstractRequestBodyParamConverterTest
{
    private $serializer;
    private $converter;

    public function setUp()
    {
        // skip the test if the installed version of SensioFrameworkExtraBundle
        // is not compatible with the RequestBodyParamConverter20 class
        $parameter = new \ReflectionParameter(
            array(
                'Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface',
                'supports',
            ),
            'configuration'
        );
        if ('Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface' !== $parameter->getClass()->getName()) {
            $this->markTestSkipped('skipping RequestBodyParamConverter20Test due to an incompatible version of the SensioFrameworkExtraBundle');
        }

        $this->serializer = $this->getMock('JMS\Serializer\SerializerInterface');
        $this->converter = $this->getMock(
            'FOS\RestBundle\Request\RequestBodyParamConverter20',
            array('getContext'),
            array($this->serializer)
        );
    }

    public function testConstructThrowsExceptionIfValidatorIsSetAndValidationArgumentIsNull()
    {
        $this->setExpectedException('InvalidArgumentException');
        new RequestBodyParamConverter20(
            $this->serializer,
            null,
            null,
            $this->getMock('Symfony\Component\Validator\ValidatorInterface')
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

        list($context) = $this->createDeserializationContext();
        $this->converter->expects($this->once())
            ->method('getContext')
            ->will($this->returnValue($context));

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

        list($context) = $this->createDeserializationContext();
        $this->converter->expects($this->once())
            ->method('getContext')
            ->will($this->returnValue($context));

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

        list($context) = $this->createDeserializationContext();
        $this->converter->expects($this->once())
            ->method('getContext')
            ->will($this->returnValue($context));

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
            'context' => array(
                'groups' => array('group1'),
                'version' => '1.0',
            ),
        );

        list($context, $jmsContext) = $this->createDeserializationContext(
            $options['context']['groups'],
            $options['context']['version']
        );

        $this->converter->expects($this->once())
            ->method('getContext')
            ->will($this->returnValue($context));

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with($requestBody, 'FOS\RestBundle\Tests\Request\Post', 'json', $jmsContext);

        $request = $this->createRequest($requestBody, 'application/json');
        $config = $this->createConfiguration('FOS\RestBundle\Tests\Request\Post', 'post', $options);

        $this->converter->apply($request, $config);
    }

    public function testApplyWithDefaultSerializerContextExclusionPolicy()
    {
        $this->converter = $this->getMock(
            'FOS\RestBundle\Request\RequestBodyParamConverter20',
            array('getContext'),
            array($this->serializer, array('group1'), '1.0')
        );

        list($context, $jmsContext) = $this->createDeserializationContext(array('group1'), '1.0');
        $request = $this->createRequest('', 'application/json');
        $config = $this->createConfiguration('FOS\RestBundle\Tests\Request\Post', 'post');

        $this->converter->expects($this->once())
            ->method('getContext')
            ->will($this->returnValue($context));

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with('', 'FOS\RestBundle\Tests\Request\Post', 'json', $jmsContext);

        $this->converter->apply($request, $config);
    }

    public function testApplyWithSerializerContextOptionsForSymfonySerializer()
    {
        $this->serializer = $this->getMock('Symfony\Component\Serializer\SerializerInterface', array('serialize', 'deserialize'));
        $this->converter = new RequestBodyParamConverter20($this->serializer);
        $requestBody = '{"name": "Post 1", "body": "This is a blog post"}';

        $options = array(
            'context' => array(
                'json_decode_options' => 2, // JSON_BIGINT_AS_STRING
            ),
        );

        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with($requestBody, 'FOS\RestBundle\Tests\Request\Post', 'json', $options['context']);

        $request = $this->createRequest($requestBody, 'application/json');
        $config = $this->createConfiguration('FOS\RestBundle\Tests\Request\Post', 'post', $options);

        $this->converter->apply($request, $config);
    }

    public function testApplyWithValidationErrors()
    {
        $validator = $this->getMockBuilder('Symfony\Component\Validator\Validator')
            ->disableOriginalConstructor()
            ->getMock();
        $validationErrors = $this->getMock('Symfony\Component\Validator\ConstraintViolationList');

        $this->converter = new RequestBodyParamConverter20($this->serializer, null, null, $validator, 'validationErrors');

        $expectedPost = new Post('Post 1', 'This is a blog post');
        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with('', 'FOS\RestBundle\Tests\Request\Post', 'json')
            ->will($this->returnValue($expectedPost));

        $request = $this->createRequest('', 'application/json');
        $options = array(
            'validator' => array(
                'groups' => array('group1'),
                'traverse' => true,
                'deep' => true,
            ),
        );

        $validator->expects($this->once())
            ->method('validate')
            ->with($expectedPost, array('group1'), true, true)
            ->will($this->returnValue($validationErrors));

        $config = $this->createConfiguration('FOS\RestBundle\Tests\Request\Post', 'post', $options);
        $this->converter->apply($request, $config);

        $this->assertSame($expectedPost, $request->attributes->get('post'));
        $this->assertSame($validationErrors, $request->attributes->get('validationErrors'));
    }

    public function testDefaultValidatorOptions()
    {
        $this->converter = new RequestBodyParamConverter20($this->serializer);
        $reflClass = new \ReflectionClass($this->converter);
        $method = $reflClass->getMethod('getValidatorOptions');
        $method->setAccessible(true);
        $options = $method->invoke($this->converter, array());

        $expected = array(
            'groups' => null,
            'traverse' => false,
            'deep' => false,
        );

        $this->assertEquals($expected, $options);
    }

    public function testDefaultValidatorOptionsMergedWithUserOptions()
    {
        // Annotation example
        // @ParamConverter(
        //   post,
        //   class="AcmeBlogBundle:Post",
        //   options={"validator"={"groups"={"Posting"}}
        // )
        $userOptions = array(
            'validator' => array(
                'groups' => array('Posting'),
            ),
        );

        $expectedOptions = array(
            'groups' => array('Posting'),
            'traverse' => false,
            'deep' => false,
        );

        $converterMock = $this->getMockBuilder('FOS\RestBundle\Request\RequestBodyParamConverter20')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $reflClass = new \ReflectionClass($converterMock);
        $method = $reflClass->getMethod('getValidatorOptions');
        $method->setAccessible(true);
        $mergedOptions = $method->invoke($converterMock, $userOptions);

        $this->assertEquals($expectedOptions, $mergedOptions);
    }

    public function testValidatorOptionsStructureAfterMergeWithUserOptions()
    {
        // Annotation example
        // @ParamConverter(
        //   post,
        //   class="AcmeBlogBundle:Post",
        //   options={"validator"={"groups"={"Posting"}}
        // )
        $userOptions = array(
            'validator' => array(
                'groups' => array('Posting'),
            ),
        );
        $config = $this->createConfiguration(null, null, $userOptions);

        $validator = $this->getMockBuilder('Symfony\Component\Validator\Validator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->converter = new RequestBodyParamConverter20($this->serializer, null, null, $validator, 'validationErrors');
        $request = $this->createRequest();

        $this->converter->apply($request, $config);
    }
}
