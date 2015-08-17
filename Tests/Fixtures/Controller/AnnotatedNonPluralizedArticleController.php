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

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 *  @Rest\RouteResource("Article", pluralize=false)
 */
class AnnotatedNonPluralizedArticleController extends FosRestController
{
    public function cgetAction()
    {} // [GET] /article

    public function getAction($slug)
    {} // [GET] /article/{slug}

    public function cgetCommentAction($slug)
    {} // [GET] /article/{slug}/comment

    public function getCommentAction($slug, $comment)
    {} // [GET] /article/{slug}/comment/{slug}
}
