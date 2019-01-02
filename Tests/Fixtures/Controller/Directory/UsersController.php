<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Fixtures\Controller\Directory;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UsersController extends AbstractController
{
    public function getUsersAction()
    {
    }

 // [GET] /users

    public function getUserAction($slug)
    {
    }

 // [GET] /users/{slug}

    public function postUsersAction()
    {
    }

 // [POST] /users

    public function putUserAction($slug)
    {
    }

 // [PUT] /users/{slug}
}
