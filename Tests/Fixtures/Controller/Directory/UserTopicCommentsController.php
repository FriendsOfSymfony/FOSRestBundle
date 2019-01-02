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

class UserTopicCommentsController extends AbstractController
{
    public function getCommentsAction($slug, $title)
    {
    }

 // [GET] /users/{slug}/topics/{title}/comments

    public function putCommentAction($slug, $title, $id)
    {
    }

 // [PUT] /users/{slug}/topics/{title}/comments/{id}
}
