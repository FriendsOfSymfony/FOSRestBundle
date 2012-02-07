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
use Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser;

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
     * @var ControllerNameParser
     */
    private $parser;

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
     * @param ContainerInterface    $container        Container
     * @param ControllerNameParser  $parser           A ControllerNameParser instance
     * @param Request               $request          Active request
     * @param QueryParamReader      $queryParamReader Query param reader
     */
    public function __construct(ContainerInterface $container, ControllerNameParser $parser, QueryParamReader $queryParamReader, Request $request)
    {
        $this->container = $container;
        $this->parser = $parser;
        $this->queryParamReader = $queryParamReader;
        $this->request = $request;
    }

    private function initParams()
    {
        $controller = $this->request->attributes->get('_controller');

        if (false === strpos($controller, '::')) {
            $count = substr_count($controller, ':');
            if (2 == $count) {
                // controller in the a:b:c notation then
                $controller = $this->parser->parse($controller);
            } elseif (1 == $count) {
                // controller in the service:method notation
                list($service, $method) = explode(':', $controller, 2);
                $class = get_class($this->container->get($service));
            } else {
                throw new \LogicException(sprintf('Unable to parse the controller name "%s".', $controller));
            }
        }

        if (empty($class)) {
            list($class, $method) = explode('::', $controller, 2);
        }

        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        $this->params = $this->queryParamReader->read(new \ReflectionClass($class), $method);
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
