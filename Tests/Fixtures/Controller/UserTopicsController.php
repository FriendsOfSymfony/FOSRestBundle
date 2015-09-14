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

class UserTopicsController extends Controller
{
    public function getTopicsAction($slug)
    {
    }

 // [GET] /users/{slug}/topics

    public function getTopicAction($slug, $title)
    {
    }

 // [GET] /users/{slug}/topics/{title}

    public function putTopicAction($slug, $title)
    {
    }

 // [PUT] /users/{slug}/topics/{title}

    public function hideTopicAction($slug, $title)
    {
    }

 // [POST] /users/{slug}/topics/{title}/hide

    // conventional HATEOAS actions below

    public function newTopicsAction($slug)
    {
    }

 // [GET] /users/{slug}/topics/new

    public function editTopicAction($slug, $title)
    {
    }

 // [GET] /users/{slug}/topics/{title}/edit

    public function removeTopicAction($slug, $title)
    {
    }

 // [GET] /remove/{slug}/topics/{title}/remove
}
