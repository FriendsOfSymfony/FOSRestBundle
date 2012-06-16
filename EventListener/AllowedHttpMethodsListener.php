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

    private $setParamsAsAttributes;

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
        /**
         * @var \Symfony\Component\Routing\Router
         */
        $router = $this->container->get('router');

        $routes = $router->getRouteCollection()->all();
        foreach ($routes as $name => $route) {
            if ($event->getRequest()->get('_route') === $name) {
                $currentRoute = $route;
                break;
            }
        }

        if (null !== $currentRoute) {
            $allowedMethods = array();
            $requirements = $currentRoute->getRequirements();
            if (isset($requirements['_method'])) {
                $allowedMethods[] = $requirements['_method'];
            }

            foreach ($routes as $route) {
                if ($currentRoute->getPattern() === $route->getPattern()) {
                    $requirements = $route->getRequirements();
                    if (isset($requirements['_method'])) {
                        $allowedMethods[] = $requirements['_method'];
                    }
                }
            }

            $allowedMethods = array_unique($allowedMethods);

            if (count($allowedMethods) > 0) {
                $event->getResponse()->headers->set('Allow', implode(', ', $allowedMethods));
            }
        }
    }
}
