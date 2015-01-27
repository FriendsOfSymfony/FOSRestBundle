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
use FOS\RestBundle\Response\AllowedMethodsLoader\AllowedMethodsLoaderInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * Listener to append Allow-ed methods for a given route/resource.
 *
 * @author Boris Guéry <guery.b@gmail.com>
 *
 * @internal
 */
class AllowedMethodsListener
{
    private $loader;

    /**
     * Constructor.
     *
     * @param AllowedMethodsLoaderInterface $loader
     */
    public function __construct(AllowedMethodsLoaderInterface $loader)
    {
        $this->loader = $loader;
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->get(FOSRestBundle::ZONE_ATTRIBUTE, true)) {
            return;
        }

        $allowedMethods = $this->loader->getAllowedMethods();

        if (isset($allowedMethods[$event->getRequest()->get('_route')])) {
            $event->getResponse()
                ->headers
                ->set('Allow', implode(', ', $allowedMethods[$event->getRequest()->get('_route')]));
        }
    }
}
