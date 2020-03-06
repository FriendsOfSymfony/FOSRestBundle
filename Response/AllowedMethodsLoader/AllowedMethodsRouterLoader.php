<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Response\AllowedMethodsLoader;

use Symfony\Component\Config\ConfigCache;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * AllowedMethodsRouterLoader implementation using RouterInterface to fetch
 * allowed http methods.
 *
 * @author Boris Guéry <guery.b@gmail.com>
 */
final class AllowedMethodsRouterLoader implements AllowedMethodsLoaderInterface, CacheWarmerInterface
{
    private $router;
    private $cache;

    public function __construct(RouterInterface $router, string $cacheDir, bool $isDebug)
    {
        $this->router = $router;
        $this->cache = new ConfigCache(sprintf('%s/allowed_methods.cache.php', $cacheDir), $isDebug);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedMethods(): array
    {
        if (!$this->cache->isFresh()) {
            $this->warmUp(null);
        }

        return require $this->cache->getPath();
    }

    /**
     * {@inheritdoc}
     */
    public function isOptional(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir): void
    {
        $processedRoutes = [];

        $routeCollection = $this->router->getRouteCollection();

        foreach ($routeCollection->all() as $name => $route) {
            if (!isset($processedRoutes[$route->getPath()])) {
                $processedRoutes[$route->getPath()] = [
                    'methods' => [],
                    'names' => [],
                ];
            }

            $processedRoutes[$route->getPath()]['names'][] = $name;
            $processedRoutes[$route->getPath()]['methods'] = array_merge(
                $processedRoutes[$route->getPath()]['methods'],
                $route->getMethods()
            );
        }

        $allowedMethods = [];

        foreach ($processedRoutes as $processedRoute) {
            if (count($processedRoute['methods']) > 0) {
                foreach ($processedRoute['names'] as $name) {
                    $allowedMethods[$name] = array_unique($processedRoute['methods']);
                }
            }
        }

        $this->cache->write(
            sprintf('<?php return %s;', var_export($allowedMethods, true)),
            $routeCollection->getResources()
        );
    }
}
