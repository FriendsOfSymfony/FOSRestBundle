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
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * @internal
 */
class VersionListener
{
    private $versionResolver;
    private $defaultVersion;

    public function __construct(VersionResolverInterface $versionResolver, ?string $defaultVersion = null)
    {
        $this->versionResolver = $versionResolver;
        $this->defaultVersion = $defaultVersion;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$request->attributes->get(FOSRestBundle::ZONE_ATTRIBUTE, true)) {
            return;
        }

        $version = $this->versionResolver->resolve($request);

        if (null === $version && null !== $this->defaultVersion) {
            $version = $this->defaultVersion;
        }

        // Return if nothing to do
        if (null === $version) {
            return;
        }

        $request->attributes->set('version', $version);
    }
}
