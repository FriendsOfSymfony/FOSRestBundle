<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Controller\Annotations;

use Symfony\Component\Routing\Annotation\Route as BaseRoute;

/**
 * Route annotation class.
 *
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"CLASS", "METHOD"})
 */
#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class Route extends BaseRoute
{
    public function __construct(
        $data = [],
        $path = null,
        string $name = null,
        array $requirements = [],
        array $options = [],
        array $defaults = [],
        string $host = null,
        $methods = [],
        $schemes = [],
        string $condition = null,
        int $priority = null,
        string $locale = null,
        string $format = null,
        bool $utf8 = null,
        bool $stateless = null,
        string $env = null
    ) {
        // BC layer for symfony < 5.2
        // Before symfony/routing 5.2 the constructor only had one parameter
        $method = new \ReflectionMethod(BaseRoute::class, '__construct');
        if (1 === $method->getNumberOfParameters()) {
            if (\is_string($data)) {
                $path = $data;
                $data = [];
            } elseif (!\is_array($data)) {
                throw new \TypeError(sprintf('"%s": Argument $data is expected to be a string or array, got "%s".', __METHOD__, get_debug_type($data)));
            }

            $data['path'] = $path;
            $data['name'] = $name;
            $data['requirements'] = $requirements;
            $data['options'] = $options;
            $data['defaults'] = $defaults;
            $data['host'] = $host;
            $data['methods'] = $methods;
            $data['schemes'] = $schemes;
            $data['condition'] = $condition;

            parent::__construct($data);
        } else {
            // BC layer for symfony < 6.0
            // The constructor parameter $data has been removed since symfony 6.0
            if ('data' === $method->getParameters()[0]->getName()) {
                parent::__construct(
                    $data,
                    $path,
                    $name,
                    $requirements,
                    $options,
                    $defaults,
                    $host,
                    $methods,
                    $schemes,
                    $condition,
                    $priority,
                    $locale,
                    $format,
                    $utf8,
                    $stateless,
                    $env
                );
            } else {
                if (\is_string($data)) {
                    $data = ['path' => $data];
                } elseif (!\is_array($data)) {
                    throw new \TypeError(sprintf('"%s": Argument $data is expected to be a string or array, got "%s".', __METHOD__, get_debug_type($data)));
                } elseif (0 !== count($data) && [] === \array_intersect(\array_keys($data), ['path', 'name', 'requirements', 'options', 'defaults', 'host', 'methods', 'schemes', 'condition', 'priority', 'locale', 'format', 'utf8', 'stateless', 'env'])) {
                    $localizedPaths = $data;
                    $data = ['path' => $localizedPaths];
                }

                parent::__construct(
                    $data['path'] ?? $path,
                    $data['name'] ?? $name,
                    $data['requirements'] ?? $requirements,
                    $data['options'] ?? $options,
                    $data['defaults'] ?? $defaults,
                    $data['host'] ?? $host,
                    $data['methods'] ?? $methods,
                    $data['schemes'] ?? $schemes,
                    $data['condition'] ?? $condition,
                    $data['priority'] ?? $priority,
                    $data['locale'] ?? $locale,
                    $data['format'] ?? $format,
                    $data['utf8'] ?? $utf8,
                    $data['stateless'] ?? $stateless,
                    $data['env'] ?? $env
                );
            }
        }

        if (!$this->getMethods()) {
            $this->setMethods((array) $this->getMethod());
        }
    }

    /**
     * @return string|null
     */
    public function getMethod()
    {
        return;
    }
}
