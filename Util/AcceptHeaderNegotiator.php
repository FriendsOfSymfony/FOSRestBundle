<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Util;

use Symfony\Component\HttpFoundation\Request;

class AcceptHeaderNegotiator implements AcceptHeaderNegotiatorInterface
{
    /**
     * @var array   Ordered array of formats (highest priority first)
     */
    private $defaultPriorities;

    /**
     * @var string  fallback format name
     */
    private $fallbackFormat;

    /**
     * @var Boolean if to consider the extension last or first
     */
    private $preferExtension;

    /**
     * Initialize AcceptHeaderNegotiator.
     *
     * @param   string  $fallbackFormat     Default fallback format
     * @param   array   $defaultPriorities  Ordered array of formats (highest priority first)
     * @param   Boolean $preferExtension    If to consider the extension last or first
     */
    public function __construct($fallbackFormat, array $defaultPriorities = array(), $preferExtension = false)
    {
        $this->defaultPriorities = $defaultPriorities;
        $this->fallbackFormat = $fallbackFormat;
        $this->preferExtension = $preferExtension;
    }

    /**
     * Detect the request format based on the priorities and the Accept header
     *
     * Note: Request "_format" parameter is considered the preferred Accept header
     *
     * @param   Request     $request          The request
     * @param   array       $priorities       Ordered array of formats (highest priority first)
     *
     * @return  void|string                 The format string
     */
    public function getBestFormat(Request $request, array $priorities = null)
    {
        if (empty($priorities)) {
            $priorities = $this->defaultPriorities;
        }

        $mimetypes = $request->splitHttpAcceptHeader($request->headers->get('Accept'), true);

        $extension = $request->get('_format');
        if (null !== $extension && $request->getMimeType($extension)) {
            if ($this->preferExtension) {
                $parameters = reset($mimetypes);
                $parameters = array('q' => $parameters['q']+1);
                $mimetypes = array($request->getMimeType($extension) => $parameters) + $mimetypes;
            } else {
                $parameters = end($mimetypes);
                $parameters = array('q' => $parameters['q']-1);
                $mimetypes[$request->getMimeType($extension)] = $parameters;
            }
        }

        if (empty($mimetypes)) {
            return null;
        }

        $catchAllEnabled = in_array('*/*', $priorities);
        $format = $this->getFormatByPriorities($request, $mimetypes, $priorities, $catchAllEnabled);

        if (null === $format) {
            $format = $this->fallbackFormat;
        }

        return $format;
    }

    /**
     * Get the format applying the supplied priorities to the mime types
     *
     * @param   Request     $request        The request
     * @param   array       $mimetypes      Ordered array of mimetypes as keys with priroties s values
     * @param   array       $priorities     Ordered array of formats (highest priority first)
     * @param   Boolean     $catchAllEnabled     If there is a catch all priority
     *
     * @return  void|string                 The format string
     */
    protected function getFormatByPriorities($request, $mimetypes, $priorities, $catchAllEnabled = false)
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
                } elseif ($catchAllEnabled) {
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
