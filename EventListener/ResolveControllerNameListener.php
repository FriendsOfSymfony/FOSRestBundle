<?php

namespace FOS\RestBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Guarantees that the _controller key is parsed into its final format.
 *
 * @author Geoffrey Pécro <geoffrey.pecro@gmail.com>
 */
class ResolveControllerNameListener
{
    public function onKernelRequest(GetResponseEvent $event)
    {
        $controller = $event->getRequest()->attributes->get('_controller');

        if (is_string($controller) && 1 === substr_count($controller, ':')) {
            $parts = explode(':', $controller);

            if (class_exists($parts[0])) {
                $controller = sprintf('%s::%s', $parts[0], $parts[1]);

                $event->getRequest()->attributes->set('_controller', $controller);
            }
        }
    }
}
