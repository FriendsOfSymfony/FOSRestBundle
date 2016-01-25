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
use FOS\RestBundle\Request\ParamFetcherInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * This listener handles various setup tasks related to the query fetcher.
 *
 * Setting the controller callable on the query fetcher
 * Setting the query fetcher as a request attribute
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 *
 * @internal
 */
class ParamFetcherListener
{
    private $paramFetcher;
    private $setParamsAsAttributes;

    /**
     * Constructor.
     *
     * @param ParamFetcherInterface $paramFetcher
     * @param bool                  $setParamsAsAttributes
     */
    public function __construct(ParamFetcherInterface $paramFetcher, $setParamsAsAttributes = false)
    {
        $this->paramFetcher = $paramFetcher;
        $this->setParamsAsAttributes = $setParamsAsAttributes;
    }

    /**
     * Core controller handler.
     *
     * @param FilterControllerEvent $event
     *
     * @throws \InvalidArgumentException
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->get(FOSRestBundle::ZONE_ATTRIBUTE, true)) {
            return;
        }

        $controller = $event->getController();

        if (is_callable($controller) && method_exists($controller, '__invoke')) {
            $controller = [$controller, '__invoke'];
        }

        $this->paramFetcher->setController($controller);
        $attributeName = $this->getAttributeName($controller);
        $request->attributes->set($attributeName, $this->paramFetcher);

        if ($this->setParamsAsAttributes) {
            $params = $this->paramFetcher->all();
            foreach ($params as $name => $param) {
                if ($request->attributes->has($name) && null !== $request->attributes->get($name)) {
                    $msg = sprintf("ParamFetcher parameter conflicts with a path parameter '$name' for route '%s'", $request->attributes->get('_route'));
                    throw new \InvalidArgumentException($msg);
                }

                $request->attributes->set($name, $param);
            }
        }
    }

    /**
     * Determines which attribute the ParamFetcher should be injected as.
     *
     * @param callable $controller The controller action as an "array" callable.
     *
     * @return string
     */
    private function getAttributeName(callable $controller)
    {
        list($object, $name) = $controller;
        $method = new \ReflectionMethod($object, $name);
        foreach ($method->getParameters() as $param) {
            if ($this->isParamFetcherType($param)) {
                return $param->getName();
            }
        }

        // If there is no typehint, inject the ParamFetcher using a default name.
        return 'paramFetcher';
    }

    /**
     * Returns true if the given controller parameter is type-hinted as
     * an instance of ParamFetcher.
     *
     * @param \ReflectionParameter $controllerParam A parameter of the controller action.
     *
     * @return bool
     */
    private function isParamFetcherType(\ReflectionParameter $controllerParam)
    {
        $type = $controllerParam->getClass();
        if (null === $type) {
            return false;
        }

        return $type->implementsInterface(ParamFetcherInterface::class);
    }
}
