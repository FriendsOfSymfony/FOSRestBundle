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
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Controllers using the View functionality of FOSRestBundle.
 *
 * @deprecated since FOSRestBundle 2.5, use {@see AbstractFOSRestController} instead
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
abstract class FOSRestController extends Controller
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
}
