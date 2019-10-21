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
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\EventListener\ExceptionListener as HttpKernelExceptionListener;
use Symfony\Component\HttpKernel\KernelEvents;

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
    private $dispatcher;

    public function __construct($controller, ?LoggerInterface $logger, EventDispatcherInterface $dispatcher)
    {
        $this->exceptionListener = new HttpKernelExceptionListener($controller, $logger);
        $this->dispatcher = $dispatcher;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->get(FOSRestBundle::ZONE_ATTRIBUTE, true)) {
            return;
        }

        $exception = $event->getException();
        $requestListener = function (KernelEvent $event) use (&$requestListener, $exception) {
            $request = $event->getRequest();

            if (!$event->isMasterRequest() && $request->attributes->get('exception') instanceof FlattenException) {
                $request->attributes->set('exception', $exception);
            }

            $this->dispatcher->removeListener(KernelEvents::REQUEST, $requestListener);
        };
        $this->dispatcher->addListener(KernelEvents::REQUEST, $requestListener);

        $this->exceptionListener->onKernelException($event);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => array('onKernelException', -100),
        );
    }
}
