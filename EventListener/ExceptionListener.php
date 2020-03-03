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
use Symfony\Component\Debug\Exception\FlattenException as LegacyFlattenException;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\EventListener\ErrorListener;
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
    private $innerExceptionListener;

    public function __construct($controller, ?LoggerInterface $logger, $debug = false, EventDispatcherInterface $dispatcher, $innerExceptionListener = null)
    {
        $this->exceptionListener = new ErrorListener($controller, $logger, $debug);
        $this->dispatcher = $dispatcher;
        $this->innerExceptionListener = $innerExceptionListener;
    }

    public function logKernelException(ExceptionEvent $event)
    {
        if ($this->innerExceptionListener) {
            $this->innerExceptionListener->logKernelException($event);
        }
    }

    /**
     * @param ExceptionEvent $event
     */
    public function onKernelException($event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->get(FOSRestBundle::ZONE_ATTRIBUTE, true)) {
            if (null === $this->innerExceptionListener) {
                return;
            }

            return $this->innerExceptionListener->onKernelException($event);
        }

        $exception = $event->getThrowable();

        $controllerArgsListener = function ($event) use (&$controllerArgsListener, $exception) {
            /** @var ControllerArgumentsEvent $event */
            $arguments = $event->getArguments();

            foreach ($arguments as $k => $argument) {
                if ($argument instanceof FlattenException || $argument instanceof LegacyFlattenException) {
                    $arguments[$k] = $exception;
                    $event->setArguments($arguments);

                    break;
                }
            }

            $this->dispatcher->removeListener(KernelEvents::CONTROLLER_ARGUMENTS, $controllerArgsListener);
        };

        $this->dispatcher->addListener(KernelEvents::CONTROLLER_ARGUMENTS, $controllerArgsListener);

        $this->exceptionListener->onKernelException($event);
    }

    public function removeCspHeader(ResponseEvent $event): void
    {
        if ($this->innerExceptionListener instanceof ErrorListener) {
            $this->innerExceptionListener->removeCspHeader($event);
        }
    }

    public function onControllerArguments(ControllerArgumentsEvent $event)
    {
        if ($this->innerExceptionListener instanceof ErrorListener) {
            $this->innerExceptionListener->onControllerArguments($event);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return ErrorListener::getSubscribedEvents();
    }
}
