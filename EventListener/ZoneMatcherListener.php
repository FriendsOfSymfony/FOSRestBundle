<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\EventListener;

use FOS\RestBundle\FOSRestBundle;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Matches FOSRest's zones.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 *
 * @internal
 */
class ZoneMatcherListener
{
    private $requestMatchers = [];

    public function addRequestMatcher(RequestMatcherInterface $requestMatcher)
    {
        $this->requestMatchers[] = $requestMatcher;
    }

    /**
     * Adds an optional "_fos_rest_zone" request attribute to be checked for existence by other listeners.
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        foreach ($this->requestMatchers as $requestMatcher) {
            if ($requestMatcher->matches($request)) {
                $request->attributes->set(FOSRestBundle::ZONE_ATTRIBUTE, true);

                return;
            }
        }

        $request->attributes->set(FOSRestBundle::ZONE_ATTRIBUTE, false);
    }
}
