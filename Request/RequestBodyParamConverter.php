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
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\Exception\UnsupportedFormatException;
use JMS\Serializer\Exception\Exception as SerializerException;

/**
 * @author Tyler Stroud <tyler@tylerstroud.com>
 */
class RequestBodyParamConverter implements ParamConverterInterface
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
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
            throw new UnsupportedMediaTypeHttpException($request->getContentType());
        } catch (SerializerException $e) {
            throw new BadRequestHttpException($e->getMessage());
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
