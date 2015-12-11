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

use FOS\RestBundle\Negotiation\FormatNegotiator as NewFormatNegotiator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Negotiation\FormatNegotiator as BaseFormatNegotiator;
use Negotiation\AcceptHeader;

/**
 * @deprecated since 1.8, to be removed in 2.0. Use {@link \FOS\RestBundle\Negotiation\FormatNegotiator} instead.
 */
class FormatNegotiator implements MediaTypeNegotiatorInterface
{
    private $formatNegotiator;
    private $map = array();

    public function __construct()
    {
        if (!$this instanceof NewFormatNegotiator) {
            @trigger_error(sprintf('%s is deprecated since version 1.8 and will be removed in 2.0. Use FOS\RestBundle\Negotiation\FormatNegotiator instead.', __CLASS__), E_USER_DEPRECATED);
        }

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
            return;
        }

        return $this->getFormat($mediaType);
    }

    /**
     * Detects the request format based on the priorities and the Accept header.
     *
     * @param Request $request
     *
     * @return null|string
     */
    public function getBestMediaType(Request $request)
    {
        $header = $request->headers->get('Accept');

        foreach ($this->map as $elements) {
            // Check if the current RequestMatcherInterface matches the current request
            if (!$elements[0]->matches($request)) {
                continue;
            }
            $options = &$elements[1];

            if (!empty($options['stop'])) {
                throw new StopFormatListenerException('Stopped format listener');
            }
            if (empty($options['priorities'])) {
                if (!empty($options['fallback_format'])) {
                    return $request->getMimeType($options['fallback_format']);
                }
                continue;
            }

            if (isset($options['prefer_extension']) && $options['prefer_extension'] && !isset($extensionHeader)) {
                $extension = pathinfo($request->getPathInfo(), PATHINFO_EXTENSION);

                if (!empty($extension)) {
                    // $extensionHeader will now be either a non empty string or an empty string
                    $extensionHeader = $request->getMimeType($extension);
                    if ($header && $extensionHeader) {
                        $header .= ',';
                    }
                    $header .= $extensionHeader.'; q='.$options['prefer_extension'];
                }
            }

            $mimeTypes = $this->formatNegotiator->normalizePriorities($options['priorities']);
            $mediaType = $this->formatNegotiator->getBest($header, $mimeTypes);
            if ($mediaType instanceof AcceptHeader && !$mediaType->isMediaRange()) {
                return $mediaType->getValue();
            }

            if (isset($options['fallback_format'])) {
                // if false === fallback_format then we fail here instead of considering more rules
                if (false === $options['fallback_format']) {
                    return;
                }

                // stop looking at rules since we have a fallback defined
                return $request->getMimeType($options['fallback_format']);
            }
        }

        return;
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
