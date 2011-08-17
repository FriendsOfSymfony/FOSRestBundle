<?php

namespace FOS\RestBundle\Request;

use Symfony\Component\HttpFoundation\Request;

class ContentNegotiator implements ContentNegotiatorInterface
{
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
    public function getBestMediaType(Request $request, array $priorities)
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