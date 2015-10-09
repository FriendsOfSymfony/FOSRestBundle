<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Routing\Loader;

use Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Config\FileLocatorInterface;
use FOS\RestBundle\Routing\Loader\Reader\RestControllerReader;

/**
 * RestRouteLoader REST-enabled controller router loader.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 * @author Bulat Shakirzyanov <mallluhuct@gmail.com>
 */
class RestRouteLoader extends Loader
{
    protected $container;
    protected $controllerParser;
    protected $controllerReader;
    protected $defaultFormat;
    protected $locator;

    /**
     * Initializes loader.
     *
     * @param ContainerInterface   $container
     * @param FileLocatorInterface $locator
     * @param ControllerNameParser $controllerParser
     * @param RestControllerReader $controllerReader
     * @param string               $defaultFormat
     */
    public function __construct(
        ContainerInterface $container,
        FileLocatorInterface $locator,
        ControllerNameParser $controllerParser,
        RestControllerReader $controllerReader, $defaultFormat = 'html'
    ) {
        $this->container = $container;
        $this->locator = $locator;
        $this->controllerParser = $controllerParser;
        $this->controllerReader = $controllerReader;
        $this->defaultFormat = $defaultFormat;
    }

    /**
     * Returns controller reader.
     *
     * @return RestControllerReader
     */
    public function getControllerReader()
    {
        return $this->controllerReader;
    }

    /**
     * {@inheritdoc}
     */
    public function load($controller, $type = null)
    {
        list($prefix, $class) = $this->getControllerLocator($controller);

        $collection = $this->controllerReader->read(new \ReflectionClass($class));
        $collection->prependRouteControllersWithPrefix($prefix);
        $collection->setDefaultFormat($this->defaultFormat);

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource)
            && 'rest' === $type
            && !in_array(pathinfo($resource, PATHINFO_EXTENSION), array('xml', 'yml')
        );
    }

    /**
     * Returns controller locator by it's id.
     *
     * @param string $controller
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    private function getControllerLocator($controller)
    {
        $class = null;
        $prefix = null;

        if (0 === strpos($controller, '@')) {
            $file = $this->locator->locate($controller);
            $controllerClass = $this->findClass($file);

            if (false === $controllerClass) {
                throw new \InvalidArgumentException(sprintf('Can\'t find class for controller "%s"', $controller));
            }

            $controller = $controllerClass;
        }

        if ($this->container->has($controller)) {
            // service_id
            $prefix = $controller.':';
            $this->container->enterScope('request');
            $this->container->set('request', new Request());
            $class = get_class($this->container->get($controller));
            $this->container->leaveScope('request');
        } elseif (class_exists($controller)) {
            // full class name
            $class = $controller;
            $prefix = $class.'::';
        } elseif (false !== strpos($controller, ':')) {
            // bundle:controller notation
            try {
                $notation = $this->controllerParser->parse($controller.':method');
                list($class) = explode('::', $notation);
                $prefix = $class.'::';
            } catch (\Exception $e) {
                throw new \InvalidArgumentException(
                    sprintf('Can\'t locate "%s" controller.', $controller)
                );
            }
        }

        if (empty($class)) {
            throw new \InvalidArgumentException(sprintf(
                'Class could not be determined for Controller identified by "%s".', $controller
            ));
        }

        return array($prefix, $class);
    }

    /**
     * Returns the full class name for the first class in the file.
     *
     * @param string $file A PHP file path
     *
     * @return string|false Full class name if found, false otherwise
     */
    protected function findClass($file)
    {
        $class = false;
        $namespace = false;
        $tokens = token_get_all(file_get_contents($file));
        for ($i = 0, $count = count($tokens); $i < $count; ++$i) {
            $token = $tokens[$i];

            if (!is_array($token)) {
                continue;
            }

            if (true === $class && T_STRING === $token[0]) {
                return $namespace.'\\'.$token[1];
            }

            if (true === $namespace && T_STRING === $token[0]) {
                $namespace = '';
                do {
                    $namespace .= $token[1];
                    $token = $tokens[++$i];
                } while ($i < $count && is_array($token) && in_array($token[0], array(T_NS_SEPARATOR, T_STRING)));
            }

            if (T_CLASS === $token[0]) {
                $class = true;
            }

            if (T_NAMESPACE === $token[0]) {
                $namespace = true;
            }
        }

        return false;
    }
}
