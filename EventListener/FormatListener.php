<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent,
    Symfony\Component\Serializer\SerializerInterface,
    Symfony\Component\HttpKernel\Exception\HttpException,
    Symfony\Component\HttpKernel\HttpKernelInterface;

use FOS\RestBundle\Response\Codes;

/**
 * This listener handles Accept header format negotiations.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class FormatListener
{
    /**
     * @var     array   Ordered array of formats (highest priority first)
     */
    protected $defaultPriorities;

    /**
     * @var     string  fallback format name
     */
    protected $fallbackFormat;

    /**
     * Initialize FormatListener.
     *
     * @param   string  $fallbackFormat     Default fallback format
     * @param   array   $defaultPriorities  Ordered array of formats (highest priority first)
     */
    public function __construct($fallbackFormat, array $defaultPriorities = array())
    {
        $this->defaultPriorities = $defaultPriorities;
        $this->fallbackFormat = $fallbackFormat;
    }

    /**
     * Determines and sets the Request format
     *
     * @param   GetResponseEvent   $event    The event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();

/*
        // TODO get priorities from the controller action
        $action = $request->attributes->get('_controller');
        $controller = $event->getController();
        $priorities =
*/
        if (empty($priorities)) {
            $priorities = $this->defaultPriorities;
        }

        $format = null;
        if (!empty($priorities)) {
            $format = $this->detectFormat($request, $priorities);
        }

        if (null === $format) {
            $format = $this->fallbackFormat;
        }

        if (null === $format) {
            if ($event->getRequestType() === HttpKernelInterface::MASTER_REQUEST)  {
                throw new HttpException(Codes::HTTP_NOT_ACCEPTABLE, "No matching accepted Response format could be determined");
            }

            return;
        }

        $request->setRequestFormat($format);
    }

    /**
     * Detect the request format based on the priorities and the Accept header
     *
     * Note: Request "_format" parameter is considered the preferred Accept header
     *
     * @param   Request     $request        The request
     * @param   array       $priorities     Ordered array of formats (highest priority first)
     *
     * @return  void|string                 The format string
     */
    protected function detectFormat($request, $priorities)
    {
        $mimetypes = $request->splitHttpAcceptHeader($request->headers->get('Accept'));

        $extension = $request->get('_format');
        if (null !== $extension) {
            $mimetypes[$request->getMimeType($extension)] = reset($mimetypes)+1;
            arsort($mimetypes);
        }

        if (empty($mimetypes)) {
            return null;
        }

        $catch_all_priority = in_array('*/*', $priorities);
        return $this->getFormatByPriorities($request, $mimetypes, $priorities, $catch_all_priority);
    }

    /**
     * Get the format applying the supplied priorities to the mime types
     *
     * @param   Request     $request        The request
     * @param   array       $mimetypes      Ordered array of mimetypes as keys with priroties s values
     * @param   array       $priorities     Ordered array of formats (highest priority first)
     * @param   Boolean     $catch_all_priority     If there is a catch all priority
     *
     * @return  void|string                 The format string
     */
    protected function getFormatByPriorities($request, $mimetypes, $priorities, $catch_all_priority = false)
    {
        $max = reset($mimetypes);
        $keys = array_keys($mimetypes, $max);

        $formats = array();
        foreach ($keys as $mimetype) {
            unset($mimetypes[$mimetype]);
            if ($mimetype === '*/*') {
                return reset($priorities);
            }
            $format = $request->getFormat($mimetype);
            if ($format) {
                $priority = array_search($format, $priorities);
                if (false !== $priority) {
                    $formats[$format] = $priority;
                } elseif ($catch_all_priority) {
                    $formats[$format] = count($priorities);
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
