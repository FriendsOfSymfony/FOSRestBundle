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
     * @var     array   Ordered array of formats (highest priority first)
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
     * @param   array   $defaultPriorities  Ordered array of formats (highest priority first)
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
     * @param   array       $priorities     Ordered array of formats (highest priority first)
     */
    protected function detectFormat($request, $priorities)
    {
        $mimetypes = $request->splitHttpAcceptHeader($request->headers->get('Accept'));

        $extension = $request->get('_format');
        if (null !== $extension) {
            $mimetypes[$request->getMimeType($extension)] = reset($mimetypes)+1;
        }

        if (!empty($mimetypes)) {
            $catch_all_priority = in_array('*/*', $priorities) ? count($priorities) : false;
            $format = $this->getFormatByPriorities($request, $mimetypes, $priorities, $catch_all_priority);
        }

        if (null === $format) {
            $format = $this->defaultFormat;
        }

        if (null === $format) {
            $request->setRequestFormat($format);
        }
    }

    /**
     * Get the format applying the supplied priorities to the mime types
     *
     * @param   Request     $request        The request
     * @param   array       $mimetypes      Ordered array of mimetypes as keys with priroties s values
     * @param   array       $priorities     Ordered array of formats (highest priority first)
     * @param   integer|Boolean     $catch_all_priority     If there is a catch all priority
     *
     * @return  void|string                     The format string
     */
    protected function getFormatByPriorities($request, $mimetypes, $priorities, $catch_all_priority = false)
    {
        $max = reset($mimetypes);
        $keys = array_keys($mimetypes, $max);

        $formats = array();
        foreach ($keys as $mimetype) {
            unset($mimetypes[$mimetype]);
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

        if (empty($formats) && !empty($mimetypes)) {
            return $this->getFormatByPriorities($request, $mimetypes, $priorities);
        }

        asort($formats);

        return key($formats);
    }
}
