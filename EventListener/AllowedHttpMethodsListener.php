<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent,
    Symfony\Component\DependencyInjection\ContainerInterface;

use FOS\RestBundle\CacheWarmer\AllowedHttpMethodsCacheWarmer;

/**
 * Listener to append Allow-ed methods for a given route/resource
 *
 * @author Boris Guéry <guery.b@gmail.com>
 */
class AllowedHttpMethodsListener
{
    /**
     * @var AllowedHttpMethodsCacheWarmer
     */
    private $cacheWarmer;

    /**
     * @var string
     */
    private $kernelCacheDir;

    /**
     * Constructor.
     *
     * @param AllowedHttpMethodsCacheWarmer $cacheWarmer
     * @param %kernel.cache_dir%            $kernelCacheDir
     */
    public function __construct(AllowedHttpMethodsCacheWarmer $cacheWarmer, $kernelCacheDir)
    {
        $this->cacheWarmer    = $cacheWarmer;
        $this->kernelCacheDir = $kernelCacheDir;
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $cacheFile = $this->kernelCacheDir . '/fos_rest/allowed_http_methods.php';

        if (!is_file($cacheFile)) {
            $this->cacheWarmer->warmUp($this->kernelCacheDir);
        }

        $allowedMethods = require $cacheFile;

        if (isset($allowedMethods[$event->getRequest()->get('_route')])) {

            $event->getResponse()
                ->headers
                ->set('Allow', implode(', ', $allowedMethods[$event->getRequest()->get('_route')]));
        }
    }
}
