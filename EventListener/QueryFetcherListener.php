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

use Symfony\Component\HttpKernel\Event\FilterControllerEvent,
    Symfony\Component\HttpKernel\HttpKernelInterface,
    Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This listener handles various setup tasks related to the query fetcher
 *
 * Setting the controller callable on the query fetcher
 * Setting the query fetcher as a request attribute
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class QueryFetcherListener
{
    /**
     * @var ContainerInterface
     */
    private $container;

    private $setParamsAsAttributes;

    /**
     * Constructor.
     *
     * @param   ContainerInterface $container container
     */
    public function __construct(ContainerInterface $container, $setParamsAsAttributes = false)
    {
        $this->container = $container;
        $this->setParamsAsAttributes = $setParamsAsAttributes;
    }

    /**
     * Core controller handler
     *
     * @param   FilterControllerEvent   $event    The event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();
        $queryFetcher = $this->container->get('fos_rest.request.query_fetcher');

        $queryFetcher->setController($event->getController());
        $request->attributes->set('queryFetcher', $queryFetcher);

        if ($this->setParamsAsAttributes) {
            $params = $queryFetcher->all();
            foreach ($params as $name => $param) {
                if ($request->attributes->has($name)) {
                    $msg = sprintf("QueryFetcher parameter conflicts with a path parameter '$name' for route '%s'", $request->attributes->get('_route'));
                    throw new \InvalidArgumentException($msg);
                }

                $request->attributes->set($name, $param);
            }
        }
    }
}
