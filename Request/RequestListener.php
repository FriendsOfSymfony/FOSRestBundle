<?php

namespace FOS\RestBundle\Request;

use Symfony\Component\HttpFoundation\ParameterBag,
    Symfony\Component\HttpKernel\Event\GetResponseEvent,
    Symfony\Component\Serializer\SerializerInterface,
    Symfony\Component\Serializer\Encoder\DecoderInterface;

/*
 * This file is part of the FOSRestBundle
 *
 * (c) Lukas Kahwe Smith <smith@pooteeweet.org>
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 * (c) Bulat Shakirzyanov <avalanche123>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * This listener handles Request body decoding.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class RequestListener
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * Set a serializer instance
     *
     * @param   SerializerInterface $serializer A serializer instance with all relevant encoders (lazy) loaded
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Core request handler
     *
     * @param   GetResponseEvent   $event    The event
     */
    public function onCoreRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (0 == count($request->request->all())
            && in_array($request->getMethod(), array('POST', 'PUT', 'DELETE'))
        ) {
            $format = $request->getFormat($request->headers->get('Content-Type'));
            if (null === $format) {
                return;
            }

            $encoder = $this->serializer->getEncoder($format);
            if (!($encoder instanceof DecoderInterface)) {
                return;
            }

            $data = $encoder->decode($request->getContent(), $format);
            $request->request = new ParameterBag((array)$data);
        }
    }
}
