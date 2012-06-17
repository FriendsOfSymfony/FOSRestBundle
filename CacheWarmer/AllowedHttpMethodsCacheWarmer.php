<?php

namespace FOS\RestBundle\CacheWarmer;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer,
    Symfony\Component\DependencyInjection\ContainerInterface;

class AllowedHttpMethodsCacheWarmer extends CacheWarmer
{

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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
    function isOptional()
    {
        return true;
    }

    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     */
    function warmUp($cacheDir)
    {
        /**
         * @var \Symfony\Component\Routing\Router
         */
        $router = $this->container->get('router');
        $processedRoutes = array();

        foreach ($router->getRouteCollection()->all() as $name => $route) {

            if (!isset($processedRoutes[$route->getPattern()])) {
                $processedRoutes[$route->getPattern()] = array(
                    'methods' => array(),
                    'names'   => array(),
                );
            }

            $processedRoutes[$route->getPattern()]['names'][] = $name;

            $requirements = $route->getRequirements();
            if (isset($requirements['_method'])) {
                $processedRoutes[$route->getPattern()]['methods'][] = $requirements['_method'];
            }
        }

        $allowedMethods = array();

        foreach ($processedRoutes as $processedRoute) {
            if (count($processedRoute['methods']) > 0) {
                foreach ($processedRoute['names'] as $name) {
                    $allowedMethods[$name] = $processedRoute['methods'];
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
