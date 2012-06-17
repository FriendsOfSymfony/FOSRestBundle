<?php
namespace FOS\RestBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent,
    Symfony\Component\HttpKernel\HttpKernelInterface,
    Symfony\Component\DependencyInjection\ContainerInterface;

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
