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

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\EventListener\ExceptionListener;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * This listener handles ensures that for specific formats AccessDeniedExceptions
 * will return a 403 regardless of how the firewall is configured
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class AccessDeniedListener extends ExceptionListener
{
    private $formats;
    private $challenge;

    /**
     * Constructor.
     *
     * @param array           $formats    An array with keys corresponding to request formats or content types
     *                                    that must be processed by this listener
     * @param string          $challenge
     * @param string          $controller
     * @param LoggerInterface $logger
     */
    public function __construct($formats, $challenge, $controller, LoggerInterface $logger = null)
    {
        $this->formats = $formats;
        $this->challenge = $challenge;
        parent::__construct($controller, $logger);
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        static $handling;

        if (true === $handling) {
            return false;
        }

        $request = $event->getRequest();

        if (empty($this->formats[$request->getRequestFormat()]) && empty($this->formats[$request->getContentType()])) {
            return false;
        }

        $handling = true;

        $exception = $event->getException();

        if ($exception instanceof AccessDeniedException) {
            $exception = new AccessDeniedHttpException('You do not have the necessary permissions', $exception);
            $event->setException($exception);
            parent::onKernelException($event);
        } elseif ($exception instanceof AuthenticationException) {
            if ($this->challenge) {
                $exception = new UnauthorizedHttpException($this->challenge, 'You are not authenticated', $exception);
            } else {
                $exception = new HttpException(401, 'You are not authenticated', $exception);
            }
            $event->setException($exception);
            parent::onKernelException($event);
        }

        $handling = false;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => array('onKernelException', 5),
        );
    }
}
