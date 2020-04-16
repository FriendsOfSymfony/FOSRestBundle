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

@trigger_error(sprintf('The %s\RestRouteLoader class is deprecated since FOSRestBundle 2.8.', __NAMESPACE__), E_USER_DEPRECATED);

use FOS\RestBundle\Routing\Loader\Reader\RestControllerReader;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;

/**
 * RestRouteLoader REST-enabled controller router loader.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 * @author Bulat Shakirzyanov <mallluhuct@gmail.com>
 *
 * @deprecated since 2.8
 */
class RestRouteLoader extends Loader
{
    protected $container;
    protected $controllerParser;
    protected $controllerReader;
    protected $defaultFormat;
    protected $locator;

    /**
     * @param RestControllerReader $controllerReader
     * @param string               $defaultFormat
     */
    public function __construct(
        ContainerInterface $container,
        FileLocatorInterface $locator,
        $controllerReader,
        $defaultFormat = 'html'
    ) {
        $this->container = $container;
        $this->locator = $locator;

        if ($controllerReader instanceof ControllerNameParser || null === $controllerReader) {
            @trigger_error(sprintf('Not passing an instance of %s as the 3rd argument of %s() is deprecated since FOSRestBundle 2.8.', RestControllerReader::class, __METHOD__), E_USER_DEPRECATED);

            $this->controllerParser = $controllerReader;

            if (!$defaultFormat instanceof RestControllerReader) {
                throw new \TypeError(sprintf('Argument 4 passed to %s() must be an instance of %s, %s given.', __METHOD__, RestControllerReader::class, is_object($defaultFormat) ? get_class($defaultFormat) : gettype($defaultFormat)));
            }

            $this->controllerReader = $defaultFormat;
            $this->defaultFormat = func_num_args() > 4 ? func_get_arg(4) : 'html';
        } elseif (!$controllerReader instanceof RestControllerReader) {
            throw new \TypeError(sprintf('Argument 3 passed to %s() must be an instance of %s, %s given.', __METHOD__, RestControllerReader::class, is_object($controllerReader) ? get_class($controllerReader) : gettype($controllerReader)));
        } else {
            $this->controllerReader = $controllerReader;
            $this->defaultFormat = $defaultFormat;
        }
    }

    /**
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
            && !in_array(
                pathinfo($resource, PATHINFO_EXTENSION),
                ['xml', 'yml', 'yaml']
            );
    }

    private function getControllerLocator(string $controller): array
    {
        $class = null;
        $prefix = null;

        if (0 === strpos($controller, '@')) {
            $file = $this->locator->locate($controller);
            $controllerClass = ClassUtils::findClassInFile($file);

            if (null === $controllerClass) {
                throw new \InvalidArgumentException(sprintf('Can\'t find class for controller "%s"', $controller));
            }

            $controller = $controllerClass;
        }

        if ($this->container->has($controller)) {
            // service_id
            $prefix = $controller.':';

            if (Kernel::VERSION_ID >= 40100) {
                $prefix .= ':';
            }

            $useScope = method_exists($this->container, 'enterScope') && $this->container->hasScope('request');
            if ($useScope) {
                $this->container->enterScope('request');
                $this->container->set('request', new Request());
            }
            $class = get_class($this->container->get($controller));
            if ($useScope) {
                $this->container->leaveScope('request');
            }
        } elseif (class_exists($controller)) {
            // full class name
            $class = $controller;
            $prefix = $class.'::';
        } elseif ($this->controllerParser && false !== strpos($controller, ':')) {
            // bundle:controller notation
            try {
                $notation = $this->controllerParser->parse($controller.':method');
                list($class) = explode('::', $notation);
                $prefix = $class.'::';
            } catch (\Exception $e) {
                throw new \InvalidArgumentException(sprintf('Can\'t locate "%s" controller.', $controller));
            }
        }

        if (empty($class)) {
            throw new \InvalidArgumentException(sprintf('Class could not be determined for Controller identified by "%s".', $controller));
        }

        return [$prefix, $class];
    }
}
