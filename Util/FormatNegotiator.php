<?php

/*
 * This file is part of the FOSRest package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Util;

use Symfony\Component\HttpFoundation\Request;
use Negotiation\FormatNegotiator as BaseFormatNegotiator;

class FormatNegotiator implements FormatNegotiatorInterface
{
    public function __construct()
    {
        $this->formatNegotiator = new BaseFormatNegotiator();
    }

    /**
     * Detect the request format based on the priorities and the Accept header
     *
     * Note: Request "_format" parameter is considered the preferred Accept header
     *
     * @param   Request         $request          The request
     * @param   array           $priorities       Ordered array of formats (highest priority first)
     * @param   Boolean|String  $preferExtension  If to consider the extension last or first, optionally
     *                                            a q-value to use for the mimetype of the format (2.0 is the default)
     *
     * @return  void|string                       The format string
     */
    public function getBestFormat(Request $request, array $priorities, $preferExtension = false)
    {
        $acceptHeader = $request->headers->get('Accept');

        if ($preferExtension) {
            $extension = $request->get('_format');
            if (null !== $extension && $request->getMimeType($extension)) {
                if ($acceptHeader) {
                    $acceptHeader.= ',';
                }

                if (is_bool($preferExtension)) {
                    $preferExtension = '2.0';
                }

                $acceptHeader.= $request->getMimeType($extension).'; q='.$preferExtension;
            }
        }

        return $this->formatNegotiator->getBestFormat($acceptHeader, $priorities);
    }

    /**
     * Register a new format with its mime types.
     *
     * @param string  $format
     * @param array   $mimeTypes
     * @param boolean $override
     */
    public function registerFormat($format, array $mimeTypes, $override = false)
    {
        $this->formatNegotiator->registerFormat($format, $mimeTypes, $override);
    }

    /**
     * Returns the format for a given mime type, or null
     * if not found.
     *
     * @param string $mimeType
     *
     * @return string|null
     */
    public function getFormat($mimeType)
    {
        $this->formatNegotiator->getFormat($mimeType);
    }
}
