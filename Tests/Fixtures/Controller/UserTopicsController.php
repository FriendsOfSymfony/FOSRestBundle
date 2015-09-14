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
    /**
     * [GET] /users/{slug}/topics.
     *
     * @param $slug
     */
    public function getTopicsAction($slug)
    {
    }

    /**
     * [GET] /users/{slug}/topics/{title}.
     *
     * @param $slug
     * @param $title
     */
    public function getTopicAction($slug, $title)
    {
    }

    /**
     * [PUT] /users/{slug}/topics/{title}.
     *
     * @param $slug
     * @param $title
     */
    public function putTopicAction($slug, $title)
    {
    }

    /**
     * [POST] /users/{slug}/topics/{title}/hide.
     *
     * @param $slug
     * @param $title
     */
    public function hideTopicAction($slug, $title)
    {
    }

    // conventional HATEOAS actions below

    /**
     * [GET] /users/{slug}/topics/new.
     *
     * @param $slug
     */
    public function newTopicsAction($slug)
    {
    }

    /**
     * [GET] /users/{slug}/topics/{title}/edit.
     *
     * @param $slug
     * @param $title
     */
    public function editTopicAction($slug, $title)
    {
    }

    /**
     * [GET] /remove/{slug}/topics/{title}/remove.
     *
     * @param $slug
     * @param $title
     */
    public function removeTopicAction($slug, $title)
    {
    }
}
