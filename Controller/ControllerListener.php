<?php

namespace FOS\RestBundle\Controller;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent,
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
 * This listener handles Accept header format negotiations.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class ControllerListener
{
    /**
     * @var     array   Order array of formats (highest priority first)
     */
    protected $defaultPriorities;

    /**
     * @var     string  default format name
     */
    protected $defaultFormat;

    /**
     * Initialize ControllerListener.
     *
     * @param   string  $defaultFormat      Default fallback format
     * @param   array   $defaultPriorities  Order array of formats (highest priority first)
     */
    public function __construct($defaultFormat, array $defaultPriorities = array())
    {
        $this->defaultPriorities = $defaultPriorities;
        $this->defaultFormat = $defaultFormat;
    }

    /**
     * Core request handler
     *
     * @param   GetResponseEvent   $event    The event
     */
    public function onCoreController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();
        // TODO get priorities from the controller action
/*
        $action = $request->attributes->get('_controller');
        $controller = $event->getController();
        $priorities =
*/
        if (empty($priorities)) {
            $priorities = $this->defaultPriorities;
        }

        if (!empty($priorities)) {
            $this->detectFormat($request, $priorities);
        } elseif (null !== $this->defaultFormat && null === $request->get('_format')) {
            $request->setRequestFormat($this->defaultFormat);
        }
    }

    /**
     * Detect the request format in the following order:
     * 
     * - Request
     * - Accept Header
     * - Default
     *
     * @param   Request     $request        The request
     * @param   array       $priorities     Order array of formats (highest priority first)
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
     * @param   Request     $request        The request
     * @param   array       $priorities     Order array of formats (highest priority first)
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
        $catch_all_priority = in_array('*/*', $priorities) ? count($priorities) : false;

        $formats = array();
        foreach ($keys as $mimetype) {
            $format = $request->getFormat($mimetype);
            if ($format) {
                $priority = array_search($format, $priorities);
                if (false !== $priority) {
                    $formats[$format] = $priority;
                } elseif ($catch_all_priority) {
                    $formats[$format] = $catch_all_priority;
                }
            }
        }

        asort($formats);

        return key($formats);
    }
}
