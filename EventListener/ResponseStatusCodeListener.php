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
use Symfony\Component\HttpKernel\Event\KernelEvent;
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

    public function getResponseStatusCodeFromThrowable(ExceptionEvent $event): void
    {
        if (!$this->isMainRequest($event)) {
            return;
        }

        $request = $event->getRequest();

        if (!$request->attributes->get(FOSRestBundle::ZONE_ATTRIBUTE, true)) {
            return;
        }

        $statusCode = $this->exceptionValueMap->resolveFromClassName(get_class($event->getThrowable()));

        if (is_int($statusCode)) {
            $this->responseStatusCode = $statusCode;
        }
    }

    public function setResponseStatusCode(ResponseEvent $event): void
    {
        if (!$this->isMainRequest($event)) {
            return;
        }

        if (null !== $this->responseStatusCode) {
            $event->getResponse()->setStatusCode($this->responseStatusCode);

            $this->responseStatusCode = null;
        }
    }

    private function isMainRequest(KernelEvent $event): bool
    {
        if (method_exists($event, 'isMainRequest')) {
            return $event->isMainRequest();
        }

        return $event->isMasterRequest();
    }
}
