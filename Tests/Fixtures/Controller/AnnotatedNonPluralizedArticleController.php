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

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;

/**
 *  @Rest\RouteResource("Article", pluralize=false)
 */
class AnnotatedNonPluralizedArticleController extends FOSRestController
{
    /**
     * [GET] /article.
     */
    public function cgetAction()
    {
    }

    /**
     * [GET] /article/{slug}.
     *
     * @param $slug
     */
    public function getAction($slug)
    {
    }

    /**
     * [GET] /article/{slug}/comment.
     *
     * @param $slug
     */
    public function cgetCommentAction($slug)
    {
    }

    /**
     * [GET] /article/{slug}/comment/{slug}.
     *
     * @param $slug
     * @param $comment
     */
    public function getCommentAction($slug, $comment)
    {
    }
}
