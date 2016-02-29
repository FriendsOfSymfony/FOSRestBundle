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
}
