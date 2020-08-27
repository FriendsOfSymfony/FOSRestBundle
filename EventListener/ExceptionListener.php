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

@trigger_error(sprintf('The %s\ExceptionListener class is deprecated since FOSRestBundle 2.8.', __NAMESPACE__), E_USER_DEPRECATED);

use FOS\RestBundle\FOSRestBundle;
use Symfony\Component\Debug\Exception\FlattenException as LegacyFlattenException;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\EventListener\ExceptionListener as LegacyExceptionListener;
use Symfony\Component\HttpKernel\EventListener\ErrorListener;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * ExceptionListener.
 *
 * @author Ener-Getick <egetick@gmail.com>
 *
 * @deprecated since 2.8
 */
class ExceptionListener implements EventSubscriberInterface
{
    private $exceptionListener;
    private $dispatcher;

    public function __construct($exceptionListener, EventDispatcherInterface $dispatcher)
    {
        if (!$exceptionListener instanceof ErrorListener && !$exceptionListener instanceof LegacyExceptionListener) {
            throw new \TypeError(sprintf('The first argument of %s() must be an instance of %s or %s (%s given).', __METHOD__, ErrorListener::class, LegacyExceptionListener::class, is_object($errorListener) ? get_class($errorListener) : gettype($errorListener)));
        }

        $this->exceptionListener = $exceptionListener;
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
        return [
            KernelEvents::EXCEPTION => ['onKernelException', -100],
        ];
    }
}
