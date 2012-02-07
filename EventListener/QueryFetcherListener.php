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

use Symfony\Component\HttpKernel\Event\GetResponseEvent,
    Symfony\Component\HttpKernel\HttpKernelInterface,
    Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This listener handles setting the query fetcher request attribute
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class QueryFetcherListener
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor.
     *
     * @param   ContainerInterface $container container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Core request handler
     *
     * @param   GetResponseEvent   $event    The event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $request->attributes->set('queryFetcher', $this->container->get('fos_rest.request.query_fetcher'));
    }
}
