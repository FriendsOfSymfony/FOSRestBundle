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
use Symfony\Component\Debug\Exception\FlattenException as LegacyFlattenException;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\EventListener\ErrorListener;
use Symfony\Component\HttpKernel\EventListener\ExceptionListener as LegacyHttpKernelExceptionListener;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * ExceptionListener.
 *
 * @author Ener-Getick <egetick@gmail.com>
 *
 * @internal to be removed in 3.0
 */
class LegacyExceptionListener implements EventSubscriberInterface
{
    private $exceptionListener;
    private $dispatcher;

    public function __construct($errorListener, EventDispatcherInterface $dispatcher)
    {
        if (!$errorListener instanceof ErrorListener && !$errorListener instanceof LegacyHttpKernelExceptionListener) {
            throw new \TypeError(sprintf('The first argument of %s() must be an instance of %s or %s (%s given).', __METHOD__, ErrorListener::class, LegacyHttpKernelExceptionListener::class, is_object($errorListener) ? get_class($errorListener) : gettype($errorListener)));
        }

        $this->exceptionListener = $errorListener;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param ExceptionEvent $event
     */
    public function onKernelException($event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->get(FOSRestBundle::ZONE_ATTRIBUTE, true)) {
            return;
        }

        if (method_exists($event, 'getThrowable')) {
            $exception = $event->getThrowable();
        } else {
            $exception = $event->getException();
        }

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
        $this->dispatcher->addListener(KernelEvents::CONTROLLER_ARGUMENTS, $controllerArgsListener, -100);

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
