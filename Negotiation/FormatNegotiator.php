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
use Negotiation\AcceptHeader;
use Negotiation\Negotiator as BaseNegotiator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author Guilhem Niot <guilhem@gniot.fr>
 */
final class FormatNegotiator extends BaseNegotiator
{
    private $map = [];
    private $requestStack;
    private $mimeTypes;

    public function __construct(RequestStack $requestStack, array $mimeTypes = [])
    {
        $this->requestStack = $requestStack;
        $this->mimeTypes = $mimeTypes;
    }

    public function add(RequestMatcherInterface $requestMatcher, array $options = []): void
    {
        $this->map[] = [$requestMatcher, $options];
    }

    public function getBest($header, array $priorities = [], $strict = false): ?AcceptHeader
    {
        $request = $this->getRequest();
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

                    if ($extensionHeader) {
                        $header = $extensionHeader.'; q='.$options['prefer_extension'].($header ? ','.$header : '');
                    }
                }
            }

            if ($header) {
                $mimeTypes = $this->normalizePriorities(
                    $request,
                    empty($priorities) ? $options['priorities'] : $priorities
                );

                $mimeType = parent::getBest($header, $mimeTypes);

                if (null !== $mimeType) {
                    return $mimeType;
                }
            }

            if (isset($options['fallback_format'])) {
                // if false === fallback_format then we fail here instead of considering more rules
                if (false === $options['fallback_format']) {
                    return null;
                }

                // stop looking at rules since we have a fallback defined
                return new Accept($request->getMimeType($options['fallback_format']));
            }
        }

        return null;
    }

    private function sanitize(array $values): array
    {
        return array_map(function ($value) {
            return preg_replace('/\s+/', '', strtolower($value));
        }, $values);
    }

    /**
     * @param string[] $priorities
     *
     * @return string[] formatted priorities
     */
    private function normalizePriorities(Request $request, array $priorities): array
    {
        $priorities = $this->sanitize($priorities);

        $mimeTypes = [];
        foreach ($priorities as $priority) {
            if (strpos($priority, '/')) {
                $mimeTypes[] = $priority;

                continue;
            }

            $mimeTypes = array_merge($mimeTypes, Request::getMimeTypes($priority));

            if (isset($this->mimeTypes[$priority])) {
                foreach ($this->mimeTypes[$priority] as $mimeType) {
                    $mimeTypes[] = $mimeType;
                }
            }
        }

        return $mimeTypes;
    }

    private function getRequest(): Request
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            throw new \RuntimeException('There is no current request.');
        }

        return $request;
    }
}
