<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Fixtures\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrdersController extends AbstractController
{
    /**
     * [GET] /foos.
     */
    public function getFoosAction()
    {
    }

    // conventional HATEOAS action after REST action

    /**
     * [GET] /foos/new.
     */
    public function newFoosAction()
    {
    }

    // conventional HATEOAS action before REST action

    /**
     * [GET] /bars/new.
     */
    public function newBarsAction()
    {
    }

    /**
     * [GET] /bars/custom.
     */
    public function getBarsCustomAction()
    {
    }

    /**
     * [GET] /bars/{slug}.
     *
     * @param $slug
     */
    public function getBarsAction($slug)
    {
    }
}
