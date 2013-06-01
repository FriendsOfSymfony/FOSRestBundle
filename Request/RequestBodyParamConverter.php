<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;
use JMS\Serializer\Exception\UnsupportedFormatException;
use JMS\Serializer\Exception\Exception as SerializerException;
use FOS\Rest\Util\Codes;

/**
 * @author Tyler Stroud <tyler@tylerstroud.com>
 */
class RequestBodyParamConverter implements ParamConverterInterface
{
    /**
     * @var object
     */
    protected $serializer;

    /**
     * @param object $serializer
     */
    public function __construct($serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * {@inheritDoc}
     */
    public function apply(Request $request, ConfigurationInterface $configuration)
    {
        try {
            $object = $this->serializer->deserialize(
                $request->getContent(),
                $configuration->getClass(),
                $request->getContentType()
            );
        } catch (UnsupportedFormatException $e) {
            throw new HttpException(Codes::HTTP_UNSUPPORTED_MEDIA_TYPE, $e->getMessage());
        } catch (SerializerException $e) {
            throw new HttpException(Codes::HTTP_BAD_REQUEST, $e->getMessage());
        }

        $request->attributes->set($configuration->getName(), $object);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function supports(ConfigurationInterface $configuration)
    {
        return null !== $configuration->getClass();
    }
}
