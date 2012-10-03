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

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * This listener handles ensures that for specific formats AccessDeniedExceptions
 * will return a 403 regardless of how the firewall is configured
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class AccessDeniedListener
{
    private $formats;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container             container
     * @param boolean            $setParamsAsAttributes params as attributes
     */
    public function __construct($formats = array())
    {
        $this->formats = $formats;
    }

    /**
     * @param GetResponseForExceptionEvent $event The event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        if (!$exception instanceof AccessDeniedException) {
            return;
        }

        $request = $event->getRequest();
        if (!empty($this->formats[$request->getRequestFormat()])) {
            $response = new Response('You dont have the necessary permissions', 403);
            $event->setResponse($response);
        }
    }
}
