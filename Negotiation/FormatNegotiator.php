<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Negotiation;

use FOS\RestBundle\Util\StopFormatListenerException;
use Negotiation\Accept;
use Negotiation\Negotiator as BaseNegotiator;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class FormatNegotiator extends BaseNegotiator
{
    /**
     * @var array
     */
    private $map = [];
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
    public function add(RequestMatcherInterface $requestMatcher, array $options = [])
    {
        $this->map[] = [$requestMatcher, $options];
    }

    /**
     * {@inheritdoc}
     * The best format is also determined in function of the bundle configuration.
     *
     * @throws StopFormatListenerException
     */
    public function getBest($header, array $priorities = [])
    {
        $request = $this->requestStack->getCurrentRequest();
        $header = $header ?: $request->headers->get('Accept');

        foreach ($this->map as $elements) {
            // Check if the current RequestMatcherInterface matches the current request
            if (!$elements[0]->matches($request)) {
                continue;
            }
            $options = &$elements[1]; // Do not reallow memory for this variable

            if (!empty($options['stop'])) {
                throw new StopFormatListenerException('Stopped format listener');
            }
            if (empty($options['priorities']) && empty($priorities)) {
                if (!empty($options['fallback_format'])) {
                    return new Accept($request->getMimeType($options['fallback_format']));
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

            $mimeTypes = $this->normalizePriorities(
                empty($priorities) ? $options['priorities'] : $priorities
            );
            $mimeType = parent::getBest($header, $mimeTypes);
            if ($mimeType !== null) {
                return $mimeType;
            }

            if (isset($options['fallback_format'])) {
                // if false === fallback_format then we fail here instead of considering more rules
                if (false === $options['fallback_format']) {
                    return;
                }

                // stop looking at rules since we have a fallback defined
                return new Accept($request->getMimeType($options['fallback_format']));
            }
        }
    }

    /**
     * Transform the format (json, html, ...) to their mimeType form (application/json, text/html, ...).
     *
     * @param array $priorities
     *
     * @return array formated priorities
     */
    private function normalizePriorities(array $priorities)
    {
        $request = $this->requestStack->getCurrentRequest();

        foreach ($priorities as &$priority) {
            if (($mimeType = $request->getMimeType($priority)) !== null) {
                $priority = $mimeType;
            }
        }

        return $priorities;
    }
}
