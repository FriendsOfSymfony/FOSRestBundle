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

class UserTopicCommentsController extends AbstractController
{
    /**
     * [GET] /users/{slug}/topics/{title}/comments.
     *
     * @param $slug
     * @param $title
     */
    public function getCommentsAction($slug, $title)
    {
    }

    /**
     * [PUT] /users/{slug}/topics/{title}/comments/{id}.
     *
     * @param $slug
     * @param $title
     * @param $id
     */
    public function putCommentAction($slug, $title, $id)
    {
    }

    /**
     * [POST] /users/{slug}/topics/{title}/comments/{id}/ban.
     *
     * @param $slug
     * @param $title
     * @param $id
     */
    public function banCommentAction($slug, $title, $id)
    {
    }

    // conventional HATEOAS actions below

    /**
     * [GET] /users/{slug}/topics/{title}/comments/new.
     *
     * @param $slug
     * @param $title
     */
    public function newCommentsAction($slug, $title)
    {
    }

    /**
     * [GET] /users/{slug}/topics/{title}/comments/edit.
     *
     * @param $slug
     * @param $title
     * @param $id
     */
    public function editCommentAction($slug, $title, $id)
    {
    }

    /**
     * [GET] /users/{slug}/topics/{title}/comments/remove.
     *
     * @param $slug
     * @param $title
     * @param $id
     */
    public function removeCommentAction($slug, $title, $id)
    {
    }
}
