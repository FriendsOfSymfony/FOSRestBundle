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

use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class ArticleController extends FosRestController implements ClassResourceInterface
{
    public function optionsAction()
    {} // [OPTION] /articles

    public function cgetAction()
    {} // [GET] /articles

    public function getAction($slug)
    {} // [GET] /articles/{slug}

    public function cpostAction()
    {} // [POST] /articles

    public function cpatchAction()
    {} // [PATCH] /articles

    public function putAction($slug)
    {} // [PUT] /articles/{slug}

    public function patchAction($slug)
    {} // [PATCH] /articles/{slug}

    public function lockAction($slug)
    {} // [PATCH] /articles/{slug}/lock

    public function getCommentsAction($slug)
    {} // [GET] /articles/{slug}/comments

    public function getCommentAction($slug, $id)
    {} // [GET] /articles/{slug}/comments/{id}

    public function deleteCommentAction($slug, $id)
    {} // [DELETE] /articles/{slug}/comments/{id}

    public function banAction($slug, $id)
    {} // [PATCH] /articles/{slug}/ban

    public function postCommentVoteAction($slug, $id)
    {} // [POST] /articles/{slug}/comments/{id}/vote

    public function _articlebarAction()
    {} // NO route

    public function check_articlenameAction()
    {} // [GET] /articles/check_articlename

    // conventional HATEOAS actions below

    public function newAction()
    {
    } // [GET] /articles/new

    public function editAction($slug)
    {} // [GET] /article/{slug}/edit

    public function removeAction($slug)
    {} // [GET] /article/{slug}/remove

    public function newCommentAction($slug)
    {} // [GET] /articles/{slug}/comments/new

    public function editCommentAction($slug, $id)
    {} // [GET] /articles/{slug}/comments/{id}/edit

    public function removeCommentAction($slug, $id)
    {} // [GET] /articles/{slug}/comments/{id}/remove

    public function hideCommentAction($articleId, $commentId)
    {} // [PATCH] /articles/{articleId}/comments/{commentId}/hide

    // Parameter of type Request should be ignored
    public function getVotesAction(Request $request, $slug)
    {} // [GET] /articles/{slug}/votes

    public function getVoteAction(Request $request, $slug, $id)
    {} // [GET] /articles/{slug}/votes/{id}

    public function getFoosAction($slug, Request $request)
    {} // [GET] /articles/{slug}/foos
}
