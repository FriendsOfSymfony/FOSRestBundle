<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Request;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Helper to validate query parameters from the active request.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class QueryFetcher
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var QueryParamReader
     */
    private $queryParamReader;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var array
     */
    private $params;


    /**
     * Initializes fetcher.
     *
     * @param ContainerInterface $container       Container
     * @param Request           $request          Active request
     * @param QueryParamReader  $queryParamReader Query param reader
     */
    public function __construct(ContainerInterface $container, QueryParamReader $queryParamReader, Request $request)
    {
        $this->container = $container;
        $this->queryParamReader = $queryParamReader;
        $this->request = $request;
    }

    /**
     * Get a validated query parameter.
     *
     * @param string $name    Name of the query parameter
     *
     * @return mixed Value of the parameter.
     */
    public function getParameter($name)
    {
        if (!isset($this->params)) {
            $_controller = $this->request->attributes->get('_controller');

            if (null === $_controller) {
                throw new \InvalidArgumentException('No _controller for request.');
            }

            if (false !== strpos($_controller, '::')) {
                list($class, $method) = explode('::', $this->request->attributes->get('_controller'));
            } else {
                list($controller, $method) = explode(':', $_controller);
                if (!$this->container->has($controller)) {
                    throw new \InvalidArgumentException('Controller service for request not available: '.$controller);
                }
                $controller = $this->container->get($controller);
                $class = get_class($controller);
            }

            $this->params = $this->queryParamReader->read(new \ReflectionClass($class), $method);
        }

        if (!isset($this->params[$name])) {
            throw new \InvalidArgumentException(sprintf("No @QueryParam configuration for parameter '%s'.", $name));
        }

        $param = $this->request->query->get($name, $this->params[$name]->default);

        // Set default if the requirements do not match
        if ($param !== $this->params[$name]->default
            && !preg_match('/^' . $this->params[$name]->requirements . '/xs', $param)
        ) {
            $param = $this->params[$name]->default;
        }

        return $param;
    }
}
