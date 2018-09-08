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
use FOS\RestBundle\Version\VersionResolverInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * @internal
 */
class VersionListener
{
    private $versionResolver;
    private $defaultVersion;

    public function __construct(VersionResolverInterface $versionResolver, $defaultVersion = null)
    {
        $this->versionResolver = $versionResolver;
        $this->defaultVersion = $defaultVersion;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->get(FOSRestBundle::ZONE_ATTRIBUTE, true)) {
            return;
        }

        $version = $this->versionResolver->resolve($request);
        if (false === $version && null !== $this->defaultVersion) {
            $version = $this->defaultVersion;
        }

        // Return if nothing to do
        if (false === $version) {
            return;
        }

        $request->attributes->set('version', $version);
    }
}
