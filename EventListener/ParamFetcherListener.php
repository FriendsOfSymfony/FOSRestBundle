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
use Symfony\Component\HttpKernel\Event\ControllerEvent;

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

    public function __construct(ParamFetcherInterface $paramFetcher, bool $setParamsAsAttributes = false)
    {
        $this->paramFetcher = $paramFetcher;
        $this->setParamsAsAttributes = $setParamsAsAttributes;
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $request = $event->getRequest();

        if (!$request->attributes->get(FOSRestBundle::ZONE_ATTRIBUTE, true)) {
            return;
        }

        $controller = $event->getController();

        if (is_callable($controller) && (is_object($controller) || is_string($controller)) && method_exists($controller, '__invoke')) {
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

    private function getAttributeName(callable $controller): string
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

    private function isParamFetcherType(\ReflectionParameter $controllerParam): bool
    {
        $type = $controllerParam->getType();
        foreach ($type instanceof \ReflectionUnionType ? $type->getTypes() : [$type] as $type) {
            if (null === $type || $type->isBuiltin() || !$type instanceof \ReflectionNamedType) {
                continue;
            }

            $class = new \ReflectionClass($type->getName());

            if ($class->implementsInterface(ParamFetcherInterface::class)) {
                return true;
            }
        }

        return false;
    }
}
