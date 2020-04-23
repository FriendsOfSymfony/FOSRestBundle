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
use FOS\RestBundle\Util\ExceptionValueMap;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @author Christian Flothmann <christian.flothmann@sensiolabs.de>
 */
class ResponseStatusCodeListener implements EventSubscriberInterface
{
    private $exceptionValueMap;
    private $responseStatusCode;

    public function __construct(ExceptionValueMap $exceptionValueMap)
    {
        $this->exceptionValueMap = $exceptionValueMap;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'getResponseStatusCodeFromThrowable',
            KernelEvents::RESPONSE => 'setResponseStatusCode',
        ];
    }

    /**
     * @param ExceptionEvent $event
     */
    public function getResponseStatusCodeFromThrowable($event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!$request->attributes->get(FOSRestBundle::ZONE_ATTRIBUTE, true)) {
            return;
        }

        if (method_exists($event, 'getThrowable')) {
            $throwable = $event->getThrowable();
        } else {
            $throwable = $event->getException();
        }

        $statusCode = $this->exceptionValueMap->resolveThrowable($throwable);

        if (is_int($statusCode)) {
            $this->responseStatusCode = $statusCode;
        }
    }

    /**
     * @param ResponseEvent $event
     */
    public function setResponseStatusCode($event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        if (null !== $this->responseStatusCode) {
            $event->getResponse()->setStatusCode($this->responseStatusCode);

            $this->responseStatusCode = null;
        }
    }
}
