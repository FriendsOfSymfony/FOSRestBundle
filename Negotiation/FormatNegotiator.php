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

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use FOS\RestBundle\Util\MediaTypeNegotiatorInterface;
use Negotiation\FormatNegotiator as BaseFormatNegotiator;
use Negotiation\AcceptHeader;

class FormatNegotiator extends BaseFormatNegotiator
{
    /**
     * @var array
     */
    private $map = array();
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * Constructor.
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
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
     * {@inheritDoc}
     * The best format is also determined in function of the bundle configuration.
     *
     * @param string $header     If empty replaced by the current request Accept header
     * @param array  $priorities If empty replaced by the bundle configuration
     */
    public function getBest($header, array $priorities = array())
    {
        $request = $this->requestStack->getCurrentRequest();

        if (empty($header)) {
            $header = $request->headers->get('Accept');
        }

        foreach ($this->map as $elements) {
            // Check if the current RequestMatcherInterface matches the current request
            if (null === $elements[0] || !$elements[0]->matches($request)) {
                continue;
            }

            $options = &$elements[1]; // Do not reallow memory for this variable
            if (!empty($options['stop'])) {
                throw new StopFormatListenerException('Stopped format listener');
            }
            if (empty($options['priorities']) && empty($priorities)) {
                if (!empty($options['fallback_format'])) {
                    return $request->getMimeType($options['fallback_format']);
                }
                continue;
            }
            if ($options['prefer_extension']) {
                // ensure we only need to compute $extensionHeader once
                if (!isset($extensionHeader)) {
                    if (preg_match('/.*\.([a-z0-9]+)$/', $request->getPathInfo(), $matches)) {
                        $extension = $matches[1];
                    }

                    // $extensionHeader will now be either a non empty string or an empty string
                    $extensionHeader = isset($extension) ? (string) $request->getMimeType($extension) : '';
                    if ($header && $extensionHeader) {
                        $extensionHeader = ','.$extensionHeader;
                    }
                }
                if ($extensionHeader) {
                    $header .= $extensionHeader.'; q='.$options['prefer_extension'];
                }
            }

            $mimeTypes = $this->normalizePriorities(empty($priorities) ? $options['priorities'] : $priorities);
            $mimeType = parent::getBest($header, $mimeTypes);
            if ($mimeType !== null && !$mimeType->isMediaRange()) {
                return $mimeType;
            }

            if (isset($options['fallback_format']) && false !== $options['fallback_format']) {
                // if false === fallback_format then we fail here instead of considering more rules
                if (false === $options['fallback_format']) {
                    return null;
                }

                // stop looking at rules since we have a fallback defined
                return new AcceptHeader(
                    $request->getMimeType($options['fallback_format']),
                    1 // Default quality
                );
            }
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getBestFormat($acceptHeader = '', array $priorities = array())
    {
        $mimeTypes = $this->normalizePriorities($priorities);

        if (null !== $accept = $this->getBest($acceptHeader, $mimeTypes)) {
            if (0.0 < $accept->getQuality() &&
                null !== $format = $this->getFormat($accept->getValue())
            ) {
                return $format;
            }
        }

        return null;
    }
}
