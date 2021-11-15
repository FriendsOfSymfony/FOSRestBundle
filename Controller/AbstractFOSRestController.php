<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Controller;

use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

$ref = new \ReflectionMethod(ServiceSubscriberInterface::class, 'getSubscribedServices');

// Has the ServiceSubscriberInterface a return type hint
if (null !== $ref->getReturnType()) {
    class_alias(PostSymfony6AbstractFOSRestController::class, 'FOS\RestBundle\Controller\BaseAbstractFOSRestController');
} else {
    class_alias(PreSymfony6AbstractFOSRestController::class, 'FOS\RestBundle\Controller\BaseAbstractFOSRestController');
}
/**
 * Controllers using the View functionality of FOSRestBundle.
 */
abstract class AbstractFOSRestController extends BaseAbstractFOSRestController
{
    use ControllerTrait;
    /**
     * @return ViewHandlerInterface
     */
    protected function getViewHandler()
    {
        if (!$this->viewhandler instanceof ViewHandlerInterface) {
            $this->viewhandler = $this->container->get('fos_rest.view_handler');
        }

        return $this->viewhandler;
    }
}
