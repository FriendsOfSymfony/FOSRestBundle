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
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * This listener handles ensures that for specific formats AccessDeniedExceptions
 * will return a 403 regardless of how the firewall is configured.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 *
 * @internal
 */
class AccessDeniedListener implements EventSubscriberInterface
{
    private $formats;
    private $challenge;

    /**
     * Constructor.
     *
     * @param array  $formats   An array with keys corresponding to request formats or content types
     *                          that must be processed by this listener
     * @param string $challenge
     */
    public function __construct($formats, $challenge)
    {
        $this->formats = $formats;
        $this->challenge = $challenge;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        static $handling;

        if (true === $handling) {
            return false;
        }

        $request = $event->getRequest();

        if (!$request->attributes->get(FOSRestBundle::ZONE_ATTRIBUTE, true)) {
            return false;
        }

        if (empty($this->formats[$request->getRequestFormat()]) && empty($this->formats[$request->getContentType()])) {
            return false;
        }

        $handling = true;

        $exception = $event->getException();

        if ($exception instanceof AccessDeniedException) {
            $exception = new AccessDeniedHttpException(is_null($exception->getMessage()) ? 'You do not have the necessary permissions' : $exception->getMessagee(), $exception);
            $event->setException($exception);
        } elseif ($exception instanceof AuthenticationException) {
            if ($this->challenge) {
                $exception = new UnauthorizedHttpException($this->challenge, is_null($exception->getMessage()) ? 'You are not authenticated' : $exception->getMessage(), $exception);
            } else {
                $exception = new HttpException(401, is_null($exception->getMessage()) ? 'You are not authenticated' : $exception->getMessage(), $exception);
            }
            $event->setException($exception);
        }

        $handling = false;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 5],
        ];
    }
}
