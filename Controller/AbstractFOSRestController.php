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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

// Does the AbstractController::getSubscribedServices() method have a return type hint?
if (null !== (new \ReflectionMethod(AbstractController::class, 'getSubscribedServices'))->getReturnType()) {
    /**
     * Compat class for Symfony 6.0 and newer support.
     *
     * @internal
     */
    abstract class BaseAbstractFOSRestController extends AbstractController
    {
        /**
         * {@inheritdoc}
         */
        public static function getSubscribedServices(): array
        {
            $subscribedServices = parent::getSubscribedServices();
            $subscribedServices['fos_rest.view_handler'] = ViewHandlerInterface::class;

            return $subscribedServices;
        }
    }
} else {
    /**
     * Compat class for Symfony 5.4 and older support.
     *
     * @internal
     */
    abstract class BaseAbstractFOSRestController extends AbstractController
    {
        /**
         * @return array
         */
        public static function getSubscribedServices()
        {
            $subscribedServices = parent::getSubscribedServices();
            $subscribedServices['fos_rest.view_handler'] = ViewHandlerInterface::class;

            return $subscribedServices;
        }
    }
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
