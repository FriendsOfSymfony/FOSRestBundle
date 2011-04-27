<?php

namespace FOS\RestBundle\Request;

use Symfony\Component\HttpFoundation\ParameterBag,
    Symfony\Component\HttpKernel\Event\GetResponseEvent,
    Symfony\Component\Serializer\SerializerInterface,
    Symfony\Component\Serializer\Encoder\DecoderInterface,
    Symfony\Component\Routing\RouterInterface;

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
 * RequestListener object.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class RequestListener
{
    /**
     * @param   array      $formatPriorities    Key format, value priority (empty array means no Accept header matching)
     */
    protected $formatPriorities;

    /**
     * @param string default format name
     */
    protected $defaultFormat;

    /**
     * @var Boolean if to try and decode the request body
     */
    protected $decodeBody;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var RouterInterface
     */
    protected $router;
    /**
     * Initialize RequestListener.
     *
     * @param   array       $formatPriorities   Key format, value priority (empty array means no Accept header matching)
     * @param   string      $defaultFormat      Default fallback format
     * @param   Boolean     $decodeBody         If to decode the body for parameters
     */
    public function __construct($formatPriorities, $defaultFormat, $decodeBody)
    {
        $this->formatPriorities = $formatPriorities;
        $this->defaultFormat = $defaultFormat;
        $this->decodeBody = $decodeBody;
    }

    /**
     * Set a serializer instance
     *
     * @param   SerializerInterface $serializer A serializer instance with all relevant encoders (lazy) loaded
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }


    /**
     * Set a router instance
     *
     * @param   RouterInterface $router A router instance
     */
    public function setRouter(RouterInterface $router = null)
    {
        $this->router = $router;
    }

    /**
     * Core request handler
     *
     * @param   GetResponseEvent   $event    The event
     */
    public function onCoreRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if ($this->router) {
            if ($this->serializer && !empty($this->formatPriorities)) {
                $this->detectFormat($request, $this->formatPriorities);
            } elseif (null !== $this->defaultFormat && null === $request->get('_format')) {
                $request->setRequestFormat($this->defaultFormat);
            }
        }

        if ($this->decodeBody) {
            $this->decodeBody($request);
        }
    }

    /**
     * Detect the request format in the following order:
     * 
     * - Request
     * - Accept Header
     * - Default
     *
     * @param   Request     $request    The request
     * @param   array       $formatPriorities    Key format, value priority
     */
    protected function detectFormat($request, $priorities)
    {
        $format = $request->get('_format');
        if (null === $format) {
            $format = $this->getFormatFromAcceptHeader($request, $priorities);
            if (null === $format) {
                $format = $this->defaultFormat;
            }

            $request->setRequestFormat($format);
        }
    }

    /**
     * Get the format from the Accept header
     *
     * Override this method to implement more complex Accept header negotiations
     *
     * @param   Request     $request            The request
     * @param   array       $formatPriorities   Key format, value priority
     * 
     * @return  void|string                     The format string
     */
    protected function getFormatFromAcceptHeader($request, $priorities)
    {
        $mimetypes = $request->splitHttpAcceptHeader($request->headers->get('Accept'));
        if (empty($mimetypes)) {
            return null;
        }

        $max = reset($mimetypes);
        $keys = array_keys($mimetypes, $max);
        $formats = array();
        foreach ($keys as $mimetype) {
            $format = $request->getFormat($mimetype);
            if ($format && empty($formats[$format])) {
                $formats[$format] = $max + (isset($priorities[$format]) ? $priorities[$format] : 0);
            }
        }

        arsort($formats);
        return key($formats);
    }

    /**
     * Get an encoder instance for the given format
     *
     * @param    string     $format     The format string
     * @return   void|EncoderInterface  The encoder if one can be determined
     */
    protected function getEncoder($format)
    {
        if (null === $format || null === $this->serializer) {
            return null;
        }

        $encoder = $this->serializer->getEncoder($format);
        if (empty($encoder)) {
            return null;
        }

        return $encoder;
    }

    /**
     * Decode the request body depending on the request content type
     *
     * @param   Request   $request    The request
     */
    protected function decodeBody($request)
    {
        if (0 == count($request->request->all())
            && in_array($request->getMethod(), array('POST', 'PUT', 'DELETE'))
        ) {
            $format = $request->getFormat($request->headers->get('Content-Type'));

            $encoder = $this->getEncoder($format);
            if ($encoder instanceof DecoderInterface) {
                $data = $encoder->decode($request->getContent(), $format);

                $request->request = new ParameterBag((array)$data);
            }
        }
    }
}
