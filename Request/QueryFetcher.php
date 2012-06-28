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

use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Helper to validate query parameters from the active request.
 *
 * @author Alexander <iam.asm89@gmail.com>
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class QueryFetcher implements QueryFetcherInterface
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

    /**
     * @abstract
     * @param callable $controller
     *
     * @return void
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }

    /**
     * Get a validated query parameter.
     *
     * @param string $name    Name of the query parameter
     * @param Boolean $strict If a requirement mismatch should cause an exception
     *
     * @return mixed Value of the parameter.
     */
    public function get($name, $strict = null)
    {
        if (null === $this->params) {
            $this->initParams();
        }

        if (!array_key_exists($name, $this->params)) {
            throw new \InvalidArgumentException(sprintf("No @QueryParam configuration for parameter '%s'.", $name));
        }

        $config = $this->params[$name];
        $default = $config->default;
        $param = $this->request->query->get($name, $default);

        if (null === $strict) {
            $strict = $config->strict;
        }

        // Set default if the requirements do not match
        if ("" !== $config->requirements
            && $param !== $default
            && !preg_match('#^'.$config->requirements.'$#xs', $param)
        ) {
            if ($strict) {
                throw new HttpException(400, "Query parameter value '$param', does not match requirements '{$config->requirements}'");
            }

            $param = $default;
        }

        return $param;
    }

    /**
     * Get all validated query parameter.
     *
     * @param Boolean $strict If a requirement mismatch should cause an exception
     *
     * @return array Values of all the parameters.
     */
    public function all($strict = null)
    {
        if (null === $this->params) {
            $this->initParams();
        }

        $params = array();
        foreach ($this->params as $name => $config) {
            $params[$name] = $this->get($name, $strict);
        }

        return $params;
    }

    /**
     * Initialize the parameters
     *
     * @throws \InvalidArgumentException
     */
    private function initParams()
    {
        if (empty($this->controller)) {
            throw new \InvalidArgumentException('Controller and method needs to be set via setController');
        }

        if (!is_array($this->controller) || empty($this->controller[0]) || !is_object($this->controller[0])) {
            throw new \InvalidArgumentException('Controller needs to be set as a class instance (closures/functions are not supported)');
        }

        $this->params = $this->queryParamReader->read(new \ReflectionClass(ClassUtils::getClass($this->controller[0])), $this->controller[1]);
    }
}
