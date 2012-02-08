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

use Symfony\Component\HttpFoundation\Request;

/**
 * Helper to validate query parameters from the active request.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class QueryFetcher
{
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
     * @var callable
     */
    private $controller;

    /**
     * Initializes fetcher.
     *
     * @param QueryParamReader      $queryParamReader Query param reader
     * @param Request               $request          Active request
     */
    public function __construct(QueryParamReader $queryParamReader, Request $request)
    {
        $this->queryParamReader = $queryParamReader;
        $this->request = $request;
    }

    public function setController($controller)
    {
        $this->controller = $controller;
    }

    private function initParams()
    {
        if (!is_array($this->controller) || empty($this->controller[0]) || empty($this->controller[1])) {
            throw new \InvalidArgumentException('Controller and method needs to be set via setController');
        }

        if (isset($this->controller[0]) && !is_object($this->controller[0])) {
            throw new \InvalidArgumentException('Controller needs to be set as a class instance (closures/functions are not supported)');
        }

        $this->params = $this->queryParamReader->read(new \ReflectionClass($this->controller[0]), $this->controller[1]);
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
            $this->initParams();
        }

        if (!isset($this->params[$name])) {
            throw new \InvalidArgumentException(sprintf("No @QueryParam configuration for parameter '%s'.", $name));
        }

        $config = $this->params[$name];
        $default = $config->default;
        $param = $this->request->query->get($name, $default);

        // Set default if the requirements do not match
        if ($param !== $default && !preg_match('#^' .$config->requirements . '$#xs', $param)) {
            $param = $default;
        }

        return $param;
    }
}
