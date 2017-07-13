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
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\EventListener\ExceptionListener as HttpKernelExceptionListener;
use Symfony\Component\HttpKernel\EventListener\ProfilerListener;
use Symfony\Component\HttpKernel\Profiler\Profiler;

/**
 * ExceptionListener.
 *
 * @author Ener-Getick <egetick@gmail.com>
 * @author Daniel West <daniel@silverback.is>
 *
 * @internal
 */
class ExceptionListener extends ProfilerListener
{
    private $controller;
    private $logger;
    /**
     * @var HttpKernelExceptionListener
     */
    private $twig_exception_listener;

    /**
     * Constructor.
     *
     * @param Profiler                     $profiler           A Profiler instance
     * @param RequestStack                 $requestStack       A RequestStack instance
     * @param RequestMatcherInterface|null $matcher            A RequestMatcher instance
     * @param bool                         $onlyException      true if the profiler only collects data when an exception occurs, false otherwise
     * @param bool                         $onlyMasterRequests true if the profiler only collects data when the request is a master request, false otherwise
     * @param string                       $controller         Controller to pass into twig exception listener
     * @param LoggerInterface              $logger             Logger to pass into twig exception listener
     */
    public function __construct(
        Profiler $profiler,
        RequestStack $requestStack,
        RequestMatcherInterface $matcher = null,
        $onlyException = false,
        $onlyMasterRequests = false,
        $controller,
        LoggerInterface $logger
    )
    {
        $this->controller = $controller;
        $this->logger = $logger;
        parent::__construct($profiler, $requestStack, $matcher, $onlyException, $onlyMasterRequests);
        $this->twig_exception_listener = new HttpKernelExceptionListener($controller, $logger);
    }

    /**
     * {@inheritdoc}
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->get(FOSRestBundle::ZONE_ATTRIBUTE, true)) {
            parent::onKernelException($event);
            return;
        }

        $this->twig_exception_listener->onKernelException($event);
    }
}
