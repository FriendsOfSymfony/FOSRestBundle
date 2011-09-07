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

class FormatNegotiator implements FormatNegotiatorInterface
{
    /**
     * Detect the request format based on the priorities and the Accept header
     *
     * Note: Request "_format" parameter is considered the preferred Accept header
     *
     * @param   Request     $request          The request
     * @param   array       $priorities       Ordered array of formats (highest priority first)
     * @param   Boolean     $preferExtension  If to consider the extension last or first
     *
     * @return  void|string                 The format string
     */
    public function getBestFormat(Request $request, array $priorities, $preferExtension = false)
    {
        $mimetypes = $request->splitHttpAcceptHeader($request->headers->get('Accept'));

        $extension = $request->get('_format');
        if (null !== $extension && $request->getMimeType($extension)) {
            $mimetypes[$request->getMimeType($extension)] = $preferExtension
                ? reset($mimetypes)+1
                : end($mimetypes)-1;
            arsort($mimetypes);
        }

        if (empty($mimetypes)) {
            return null;
        }

        $catchAllEnabled = in_array('*/*', $priorities);
        return $this->getFormatByPriorities($request, $mimetypes, $priorities, $catchAllEnabled);
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
