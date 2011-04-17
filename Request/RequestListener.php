<?php

namespace FOS\RestBundle\Request;

use Symfony\Component\HttpFoundation\ParameterBag,
    Symfony\Component\HttpKernel\Event\GetResponseEvent,
    Symfony\Component\Serializer\SerializerInterface,
    Symfony\Component\DependencyInjection\ContainerAwareInterface,
    Symfony\Component\DependencyInjection\ContainerInterface;

/*
 * This file is part of the FOS/RestBundle
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
class RequestListener implements ContainerAwareInterface
{
    protected $formats;
    protected $detectFormat;
    protected $defaultFormat;
    protected $decodeBody;

    /**
     * Initialize RequestListener.
     *
     * @param   boolean    $detectFormat        If to try and detect the format
     * @param   string     $defaultFormat       Default fallback format
     * @param   boolean    $decodeBody          If to decode the body for parameters
     * @param   array      $formats             The supported formats as keys, encoder service id's as values
     */
    public function __construct($detectFormat, $defaultFormat, $decodeBody, array $formats = null)
    {
        $this->detectFormat = $detectFormat;
        $this->defaultFormat = $defaultFormat;
        $this->decodeBody = $decodeBody;
        $this->formats = (array)$formats;
    }

    /**
     * Sets the Container associated with this Controller.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Core request handler
     *
     * @param   GetResponseEvent   $event    The event
     */
    public function onCoreRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if ($this->detectFormat) {
            $this->detectFormat($request);
        // TODO enable once https://github.com/symfony/symfony/pull/575 is merged
        //} elseif (null === $request->getRequestFormat(null)) {
        } elseif (null === $request->get('_format')) {
            $request->setRequestFormat($this->defaultFormat);
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
     * @param   Request   $request    The request
     */
    protected function detectFormat($request)
    {
        // TODO enable once https://github.com/symfony/symfony/pull/575 is merged
//        $format = $request->getRequestFormat(null);
        $format = $request->get('_format');
        if (null === $format) {
            $formats = $this->splitHttpAcceptHeader($request->headers->get('Accept'));
            if (!empty($formats)) {
                $format = $request->getFormat(key($formats));
            }

            if (null === $format) {
                $format = $this->defaultFormat;
            }
        }

        $request->setRequestFormat($format);
    }

    /**
     * Get an encoder instance for the given format
     *
     * @param    string $format     The format string
     * @return   EncoderInterface   The encoder
     */
    protected function getEncoder($format)
    {
        $serializer = $this->container->get('fos_rest.serializer');
        if (!$serializer->hasEncoder($format)) {
            // TODO this kind of lazy loading of encoders should be provided by the Serializer component
            $encoder = $this->container->get($this->formats[$format]);
            // Technically not needed, but this way we have the instance for encoding later on
            $serializer->setEncoder($format, $encoder);
        } else {
            $encoder = $serializer->getEncoder($format);
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
            if (null === $format || empty($this->formats[$format])) {
                return;
            }

            $encoder = $this->getEncoder($format);

            // TODO Serializer component should provide an interface to check if the Encoder supports decoding
            $post = $encoder->decode($request->getContent(), $format);

            $request->request = new ParameterBag((array)$post);
        }
    }

    /**
     * Splits an Accept-* HTTP header.
     * TODO remove once https://github.com/symfony/symfony/pull/575 is merged
     *
     * @param string $header  Header to split
     */
    private function splitHttpAcceptHeader($header)
    {
        if (!$header) {
            return array();
        }

        $values = array();
        foreach (array_filter(explode(',', $header)) as $value) {
            // Cut off any q-value that might come after a semi-colon
            if ($pos = strpos($value, ';')) {
                $q     = (float) trim(substr($value, strpos($value, '=') + 1));
                $value = trim(substr($value, 0, $pos));
            } else {
                $q = 1;
            }

            if (0 < $q) {
                $values[trim($value)] = $q;
            }
        }

        arsort($values);
        reset($values);

        return $values;
    }

}
