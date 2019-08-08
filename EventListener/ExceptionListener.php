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
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\EventListener\ExceptionListener as HttpKernelExceptionListener;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;

/**
 * ExceptionListener.
 *
 * @author Ener-Getick <egetick@gmail.com>
 *
 * @internal
 */
class ExceptionListener implements EventSubscriberInterface
{
    private $exceptionListener;

    public function __construct($controller, LoggerInterface $logger = null)
    {
        $this->exceptionListener = new HttpKernelExceptionListener($controller, $logger);
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->get(FOSRestBundle::ZONE_ATTRIBUTE, true)) {
            return;
        }

        $this->exceptionListener->onKernelException($event);
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => array('onKernelException', -100),
        );
    }

    protected function duplicateRequest(\Exception $exception, Request $request)
    {
        $attributes = array(
            '_controller' => $this->controller,
            'exception' => $exception,
            'logger' => $this->logger instanceof DebugLoggerInterface ? $this->logger : null,
        );
        $request = $request->duplicate(null, null, $attributes);
        $request->setMethod('GET');

        return $request;
    }
}
