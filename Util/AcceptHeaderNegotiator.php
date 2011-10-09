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
     * {@inheritdoc}
     */
    public function getBestFormat(Request $request, array $priorities = null, $extension = null)
    {
        if (empty($priorities)) {
            $priorities = $this->defaultPriorities;
        }

        $mimeTypes = $request->splitHttpAcceptHeader($request->headers->get('Accept'), true);

        if (null !== $extension && $request->getMimeType($extension)) {
            if ($this->preferExtension) {
                $parameters = reset($mimeTypes);
                $parameters = array('q' => $parameters['q']+1);
                $mimeTypes = array($request->getMimeType($extension) => $parameters) + $mimeTypes;
            } else {
                $parameters = end($mimeTypes);
                $parameters = array('q' => $parameters['q']-1);
                $mimeTypes[$request->getMimeType($extension)] = $parameters;
            }
        }

        if (empty($mimeTypes)) {
            return null;
        }

        // TODO also handle foo/*
        $catchAllEnabled = in_array('*/*', $priorities);
        $format = $this->getFormatByPriorities($request, $mimeTypes, $priorities, $catchAllEnabled);

        if (null === $format) {
            $format = $this->fallbackFormat;
        }

        return $format;
    }

    /**
     * Get the format applying the supplied priorities to the mime types
     *
     * @param   Request     $request        The request
     * @param   array       $mimeTypes      Ordered array of mimetypes as keys with priroties s values
     * @param   array       $priorities     Ordered array of formats (highest priority first)
     * @param   Boolean     $catchAllEnabled     If there is a catch all priority
     *
     * @return  null|string                 The format string
     */
    protected function getFormatByPriorities($request, $mimeTypes, $priorities, $catchAllEnabled = false)
    {
        $max = reset($mimeTypes);
        $keys = array_keys($mimeTypes, $max);

        $formats = array();
        foreach ($keys as $mimeType) {
            unset($mimeTypes[$mimeType]);
            if ($mimeType === '*/*') {
                return reset($priorities);
            }

            $priority = array_search($mimeType, $priorities);
            if (false !== $priority) {
                $formats[$mimeType] = $priority;
                continue;
            }

            $format = $request->getFormat($mimeType);
            if (null !== $format) {
                $priority = array_search($format, $priorities);
                if (false !== $priority) {
                    $formats[$format] = $priority;
                    continue;
                }
            }

            if ($catchAllEnabled) {
                $formats[$mimeType] = count($priorities);
            }
        }

        if (!empty($formats)) {
            asort($formats);
            return key($formats);
        }

        if (!empty($mimeTypes)) {
            return $this->getFormatByPriorities($request, $mimeTypes, $priorities);
        }

        return null;
    }
}
