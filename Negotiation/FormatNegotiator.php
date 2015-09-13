<?php

/*
 * This file is part of the FOSRest package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Negotiation;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use FOS\RestBundle\Util\MediaTypeNegotiatorInterface;
use Negotiation\FormatNegotiator as BaseFormatNegotiator;
use Negotiation\AcceptHeader;

class FormatNegotiator implements MediaTypeNegotiatorInterface
{
    private $formatNegotiator;
    private $map = array();

    public function __construct()
    {
        $this->formatNegotiator = new BaseFormatNegotiator();
    }

    /**
     * @param RequestMatcherInterface $requestMatcher
     * @param array                   $options
     */
    public function add(RequestMatcherInterface $requestMatcher, array $options = array())
    {
        $this->map[] = array($requestMatcher, $options);
    }

    /**
     * Detects the request format based on the priorities and the Accept header.
     *
     * @param Request $request
     *
     * @return null|string
     */
    public function getBestFormat(Request $request)
    {
        $mediaType = $this->getBestMediaType($request);
        if (null === $mediaType) {
            return null;
        }

        return $this->getFormat($mediaType);
    }

    /**
     * Detects the request format based on the priorities and the Accept header.
     *
     * @param Request $request
     *
     * @return null|string
     *
     * @deprecated since version 1.7, to be removed in 2.0.
     */
    public function getBestMediaType(Request $request)
    {
        foreach ($this->map as $elements) {
            if (null === $elements[0] || $elements[0]->matches($request)) {
                $options = $elements[1];
            }

            if (!empty($options['stop'])) {
                throw new StopFormatListenerException('Stopped format listener');
            }

            if (empty($options['priorities'])) {
                if (!empty($options['fallback_format'])) {
                    return $request->getMimeType($options['fallback_format']);
                }

                continue;
            }

            $acceptHeader = $request->headers->get('Accept');

            if ($options['prefer_extension']) {
                // ensure we only need to compute $extensionHeader once
                if (!isset($extensionHeader)) {
                    if (preg_match('/.*\.([a-z0-9]+)$/', $request->getPathInfo(), $matches)) {
                        $extension = $matches[1];
                    }

                    // $extensionHeader will now be either a non empty string or an empty string
                    $extensionHeader = isset($extension) ? (string) $request->getMimeType($extension) : '';
                    if ($acceptHeader && $extensionHeader) {
                        $extensionHeader = ','.$extensionHeader;
                    }
                }
                if ($extensionHeader) {
                    $acceptHeader .= $extensionHeader.'; q='.$options['prefer_extension'];
                }
            }

            $mimeTypes = $this->formatNegotiator->normalizePriorities($options['priorities']);
            $mediaType = $this->formatNegotiator->getBest($acceptHeader, $mimeTypes);
            if ($mediaType instanceof AcceptHeader && !$mediaType->isMediaRange()) {
                return $mediaType->getValue();
            }

            if (isset($options['fallback_format'])) {
                // if false === fallback_format then we fail here instead of considering more rules
                if (false === $options['fallback_format']) {
                    return null;
                }

                // stop looking at rules since we have a fallback defined
                return $request->getMimeType($options['fallback_format']);
            }
        }

        return null;
    }

    /**
     * Registers a new format with its mime types.
     *
     * @param string $format
     * @param array  $mimeTypes
     * @param bool   $override
     */
    public function registerFormat($format, array $mimeTypes, $override = false)
    {
        $this->formatNegotiator->registerFormat($format, $mimeTypes, $override);
    }

    /**
     * Returns the format for a given mime type, or null if not found.
     *
     * @param string $mimeType
     *
     * @return string|null
     */
    public function getFormat($mimeType)
    {
        return $this->formatNegotiator->getFormat($mimeType);
    }
}
