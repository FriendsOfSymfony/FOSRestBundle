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
    Symfony\Component\HttpKernel\HttpKernelInterface,
    Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Listener to append Allow-ed methods for a given route/resource
 *
 * @author Boris Guéry <guery.b@gmail.com>
 */

class AllowedHttpMethodsListener
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $cacheFile = $this->container->getParameter('kernel.cache_dir') . '/fos_rest/allowed_http_methods.php';

        if (!is_file($cacheFile)) {
            $this->container->get('fos_rest.allowed_http_methods_cache_warmer')
                ->warmUp($this->container->getParameter('kernel.cache_dir'));
        }

        $allowedMethods = require $cacheFile;

        if (isset($allowedMethods[$event->getRequest()->get('_route')])) {

            $event->getResponse()
                ->headers
                ->set(
                    'Allow',
                    implode(', ', $allowedMethods[$event->getRequest()->get('_route')])
            );
        }
    }
}
