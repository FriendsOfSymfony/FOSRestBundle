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

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class OrdersController extends Controller
{
    // conventional HATEOAS action after REST action

    public function getFoosAction()
    {} // [GET] /foos

    public function newFoosAction()
    {} // [GET] /foos/new

    // conventional HATEOAS action before REST action

    public function newBarsAction()
    {} // [GET] /bars/new

    public function getBarsCustomAction()
    {} // [GET] /bars/custom

    public function getBarsAction($slug)
    {} // [GET] /bars/{slug}
}
