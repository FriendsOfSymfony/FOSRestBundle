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

/**
 * Controllers using the View functionality of FOSRestBundle.
 */
abstract class AbstractFOSRestController extends AbstractController
{
    use ControllerTrait;

    /**
     * Get the ViewHandler.
     *
     * @return ViewHandlerInterface
     */
    protected function getViewHandler()
    {
        if (!$this->viewhandler instanceof ViewHandlerInterface) {
            $this->viewhandler = $this->container->get('fos_rest.view_handler');
        }

        return $this->viewhandler;
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public static function getSubscribedServices()
    {
        $subscribedServices = parent::getSubscribedServices();
        $subscribedServices['fos_rest.view_handler'] = ViewHandlerInterface::class;

        return $subscribedServices;
    }
}
