<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\CacheWarmer;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer,
    Symfony\Component\Routing\RouterInterface;

/**
 * CacheWarmer to generate Allow-ed for each routes
 *
 * @author Boris Guéry <guery.b@gmail.com>
 */
class AllowedHttpMethodsCacheWarmer extends CacheWarmer
{

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * Checks whether this warmer is optional or not.
     *
     * Optional warmers can be ignored on certain conditions.
     *
     * A warmer should return true if the cache can be
     * generated incrementally and on-demand.
     *
     * @return Boolean true if the warmer is optional, false otherwise
     */
    public function isOptional()
    {
        return true;
    }

    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     */
    public function warmUp($cacheDir)
    {
        $processedRoutes = array();

        foreach ($this->router->getRouteCollection()->all() as $name => $route) {

            if (!isset($processedRoutes[$route->getPattern()])) {
                $processedRoutes[$route->getPattern()] = array(
                    'methods' => array(),
                    'names'   => array(),
                );
            }

            $processedRoutes[$route->getPattern()]['names'][] = $name;

            $requirements = $route->getRequirements();
            if (isset($requirements['_method'])) {
                $methods = explode('|', $requirements['_method']);
                $processedRoutes[$route->getPattern()]['methods'] = array_merge(
                    $processedRoutes[$route->getPattern()]['methods'],
                    $methods
                );
            }
        }

        $allowedMethods = array();

        foreach ($processedRoutes as $processedRoute) {
            if (count($processedRoute['methods']) > 0) {
                foreach ($processedRoute['names'] as $name) {
                    $allowedMethods[$name] = array_unique($processedRoute['methods']);
                }
            }
        }

        if (!is_dir($cacheDir.'/fos_rest')) {
            if (false === @mkdir($cacheDir.'/fos_rest', 0777, true)) {
                throw new \RuntimeException(sprintf('Could not create cache directory "%s"', $cacheDir.'/fos_rest'));
            }
        }

        $this->writeCacheFile(
            $cacheDir.'/fos_rest/allowed_http_methods.php',
            sprintf('<?php return %s;', var_export($allowedMethods, true))
        );
    }
}
