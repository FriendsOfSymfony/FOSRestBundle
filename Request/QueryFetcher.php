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
     * @var array
     */
    private $params;

    /**
     * @var Request
     */
    private $request;

    /**
     * Initializes fetcher.
     *
     * @param Request              $request          Active request
     * @param RestQueryParamReader $queryParamReader Query param reader
     */
    public function __construct(Request $request, RestQueryParamReader $queryParamReader)
    {
        if (null === $request->attributes->get('_controller')) {
            // todo: better exception
            throw new \Exception(sprintf("No @QueryParam configuration for parameter '%s'", $name));
        }

        // todo: for now Controller::Method notation is assumed
        list($class, $method) = explode('::', $request->attributes->get('_controller'));

        $this->params = $queryParamReader->read(new \ReflectionClass($class), $method);
        $this->request = $request;
    }

    /**
     * Get a validated query parameter.
     *
     * @param string $name    Name of the query parameter
     * @param mixed  $default Default variable if the parameter is not set or doesn't match the requirements
     *
     * @return mixed Value of the parameter.
     */
    public function getParameter($name, $default)
    {
        if (!isset($this->params[$name])) {
            // todo: better exception
            throw new \Exception(sprintf("No @QueryParam configuration for parameter '%s'", $name));
        }

        $param = $this->request->get($name, $default);

        // Set default if the requirements do not match
        if ($param !== $default && !preg_match('#^' . $this->params[$name]->requires . '#xs', $param)) {
            $param = $default;
        }

        return $param;
    }
}
