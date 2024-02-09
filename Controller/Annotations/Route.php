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

use Symfony\Component\Routing\Annotation\Route as BaseAnnotationRoute;
use Symfony\Component\Routing\Attribute\Route as BaseAttributeRoute;

if (class_exists(BaseAttributeRoute::class)) {
    /**
     * Compatibility layer for Symfony 6.4 and later.
     *
     * @internal
     */
    class CompatRoute extends BaseAttributeRoute
    {
    }
} else {
    /**
     * Compatibility layer for Symfony 6.3 and earlier.
     *
     * @internal
     */
    class CompatRoute extends BaseAnnotationRoute
    {
    }
}

/**
 * Route annotation class.
 *
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"CLASS", "METHOD"})
 */
#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class Route extends CompatRoute
{
    /**
     * @param array|string      $data
     * @param array|string|null $path
     * @param string[]          $requirements
     * @param string[]|string   $methods
     * @param string[]|string   $schemes
     *
     * @throws \TypeError if the $data argument is an unsupported type
     */
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
        // Use Reflection to get the constructor from the parent class two levels up (accounting for our compat definition)
        $method = (new \ReflectionClass($this))->getParentClass()->getParentClass()->getMethod('__construct');

        // The $data constructor parameter was removed in Symfony 6.0 in favor of named arguments
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
