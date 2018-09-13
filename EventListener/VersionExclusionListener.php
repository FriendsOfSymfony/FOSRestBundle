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
use FOS\RestBundle\View\ConfigurableViewHandlerInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * @internal
 */
class VersionExclusionListener
{
    private $viewHandler;

    public function __construct(ViewHandlerInterface $viewHandler)
    {
        $this->viewHandler = $viewHandler;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->get(FOSRestBundle::ZONE_ATTRIBUTE, true)) {
            return;
        }

        if (!$request->attributes->has('version')) {
            return;
        }

        $version = $request->attributes->get('version');

        if ($this->viewHandler instanceof ConfigurableViewHandlerInterface) {
            $this->viewHandler->setExclusionStrategyVersion($version);
        }
    }
}
