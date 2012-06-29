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

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Helper to validate parameters of the active request.
 *
 * @author Alexander <iam.asm89@gmail.com>
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class ParamFetcher implements ParamFetcherInterface
{
    /**
     * @var ParamReader
     */
    private $paramReader;

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
     * @param ParamReader $paramReader Query param reader
     * @param Request     $request     Active request
     */
    public function __construct(ParamReader $paramReader, Request $request)
    {
        $this->paramReader = $paramReader;
        $this->request = $request;
    }

    /**
     * {@inheritDoc}
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }

    /**
     * {@inheritDoc}
     */
    public function get($name, $strict = null)
    {
        if (null === $this->params) {
            $this->initParams();
        }

        if (!array_key_exists($name, $this->params)) {
            throw new \InvalidArgumentException(sprintf("No @QueryParam/@RequestParam configuration for parameter '%s'.", $name));
        }

        $config = $this->params[$name];
        $default = $config->default;
        if (null === $strict) {
            $strict = $config->strict;
        }

        if ($config instanceof RequestParam) {
            $param = $this->request->request->get($name, $default);
        } elseif ($config instanceof QueryParam) {
            $param = $this->request->query->get($name, $default);
        }

        // Set default if the requirements do not match
        if ("" !== $config->requirements
            && ($param !== $default || null === $default)
            && !preg_match('#^'.$config->requirements.'$#xs', $param)
        ) {
            if ($strict) {
                $paramType = $config instanceof QueryParam ? 'Query' : 'Request';

                throw new HttpException(400, $paramType . " parameter value '$param', does not match requirements '{$config->requirements}'");
            }

            $param = null === $default ? '' : $default;
        }

        return $param;
    }

    /**
     * {@inheritDoc}
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

        $this->params = $this->paramReader->read(new \ReflectionClass(ClassUtils::getClass($this->controller[0])), $this->controller[1]);
    }
}
