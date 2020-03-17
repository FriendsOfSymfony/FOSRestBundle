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
use FOS\RestBundle\Version\ChainVersionResolver;
use FOS\RestBundle\Version\VersionResolverInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

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

    /**
     * @param RequestEvent $event
     */
    public function onKernelRequest($event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->get(FOSRestBundle::ZONE_ATTRIBUTE, true)) {
            return;
        }

        $version = $this->versionResolver->resolve($request);

        if (!$this->versionResolver instanceof ChainVersionResolver && null !== $version && !is_string($version)) {
            @trigger_error(sprintf('Not returning a string or null from %s::resolve() when implementing the %s is deprecated since FOSRestBundle 2.8.', get_class($this->versionResolver), VersionResolverInterface::class), E_USER_DEPRECATED);
        }

        if ((false === $version || null === $version) && null !== $this->defaultVersion) {
            $version = $this->defaultVersion;
        }

        // Return if nothing to do
        if (false === $version || null === $version) {
            return;
        }

        $request->attributes->set('version', $version);
    }
}
