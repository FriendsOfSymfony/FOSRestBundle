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
use FOS\RestBundle\View\ConfigurableViewHandlerInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * @internal
 */
class VersionListener
{
    private $viewHandler;
    private $versionResolver;
    private $defaultVersion;

    public function __construct(ViewHandlerInterface $viewHandler, VersionResolverInterface $versionResolver, $defaultVersion = null)
    {
        $this->viewHandler = $viewHandler;
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
        if (false === $version && $request->attributes->has('version')) {
            $version = $request->attributes->get('version');
        } elseif (false === $version && null !== $this->defaultVersion) {
            $version = $this->defaultVersion;
        }

        // Return if nothing to do
        if (false === $version) {
            return;
        }

        $request->attributes->set('version', $version);

        // Use the resolved version when rendering the response
        if ($this->viewHandler instanceof ConfigurableViewHandlerInterface) {
            $this->viewHandler->setExclusionStrategyVersion($version);
        }
    }
}
