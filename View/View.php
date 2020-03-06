<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\View;

use FOS\RestBundle\Context\Context;
use Symfony\Component\HttpFoundation\Response;

/**
 * Default View implementation.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author Lukas K. Smith <smith@pooteeweet.org>
 */
final class View
{
    private $data;
    private $statusCode;
    private $format;
    private $location;
    private $route;
    private $routeParameters;
    private $context;
    private $response;

    public static function create($data = null, ?int $statusCode = null, array $headers = []): self
    {
        return new static($data, $statusCode, $headers);
    }

    public static function createRedirect(string $url, int $statusCode = Response::HTTP_FOUND, array $headers = []): self
    {
        $view = static::create(null, $statusCode, $headers);
        $view->setLocation($url);

        return $view;
    }

    public static function createRouteRedirect(
        string $route,
        array $parameters = [],
        int $statusCode = Response::HTTP_FOUND,
        array $headers = []
    ): self {
        $view = static::create(null, $statusCode, $headers);
        $view->setRoute($route);
        $view->setRouteParameters($parameters);

        return $view;
    }

    public function __construct($data = null, ?int $statusCode = null, array $headers = [])
    {
        $this->setData($data);
        $this->setStatusCode($statusCode);

        if (!empty($headers)) {
            $this->getResponse()->headers->replace($headers);
        }
    }

    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }

    public function setHeader(string $name, string $value): self
    {
        $this->getResponse()->headers->set($name, $value);

        return $this;
    }

    public function setHeaders(array $headers): self
    {
        $this->getResponse()->headers->replace($headers);

        return $this;
    }

    public function setStatusCode(?int $code): self
    {
        if (null !== $code) {
            $this->statusCode = $code;
        }

        return $this;
    }

    public function setContext(Context $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function setFormat(string $format): self
    {
        $this->format = $format;

        return $this;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;
        $this->route = null;

        return $this;
    }

    /**
     * Sets the route (implicitly removes the location).
     */
    public function setRoute(string $route): self
    {
        $this->route = $route;
        $this->location = null;

        return $this;
    }

    public function setRouteParameters(array $parameters): self
    {
        $this->routeParameters = $parameters;

        return $this;
    }

    public function setResponse(Response $response): self
    {
        $this->response = $response;

        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->getResponse()->headers->all();
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function getRouteParameters(): ?array
    {
        return $this->routeParameters;
    }

    public function getResponse(): Response
    {
        if (null === $this->response) {
            $this->response = new Response();

            if (null !== ($code = $this->getStatusCode())) {
                $this->response->setStatusCode($code);
            }
        }

        return $this->response;
    }

    public function getContext(): Context
    {
        if (null === $this->context) {
            $this->context = new Context();
        }

        return $this->context;
    }
}
