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

class UserTopicCommentsController extends Controller
{
    public function getCommentsAction($slug, $title)
    {} // [GET] /users/{slug}/topics/{title}/comments

    public function putCommentAction($slug, $title, $id)
    {} // [PUT] /users/{slug}/topics/{title}/comments/{id}

    public function banCommentAction($slug, $title, $id)
    {} // [POST] /users/{slug}/topics/{title}/comments/{id}/ban

    // conventional HATEOAS actions below

    public function newCommentsAction($slug, $title)
    {} // [GET] /users/{slug}/topics/{title}/comments/new

    public function editCommentAction($slug, $title, $id)
    {} // [GET] /users/{slug}/topics/{title}/comments/edit

    public function removeCommentAction($slug, $title, $id)
    {} // [GET] /users/{slug}/topics/{title}/comments/remove

}
