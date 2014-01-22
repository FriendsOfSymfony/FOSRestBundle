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
    protected function createConfiguration($class = null, $name = null, $options = null)
    {
        $config = $this->getMockBuilder('Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter')
            ->disableOriginalConstructor()
            ->setMethods(array('getClass', 'getAliasName', 'getOptions', 'getName', 'allowArray'))
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
