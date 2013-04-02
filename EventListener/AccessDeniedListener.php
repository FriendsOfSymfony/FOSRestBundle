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
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
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

    /**
     * Constructor.
     *
     * @param array $formats    key value pairs of format names and if for the given format
     *                          the exception should be intercepted to return a 403
     */
    public function __construct($formats, $controller, LoggerInterface $logger = null)
    {
        $this->formats = $formats;
        parent::__construct($controller, $logger);
    }

    /**
     * @param GetResponseForExceptionEvent $event The event
     */
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
            $exception = new HttpException(401, 'You are not authenticated', $exception);
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
