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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class ResourceController extends Controller
{
    public function optionsAction()
    {} // [OPTION] /users

    public function cgetAction()
    {} // [GET] /users

    public function getAction($slug)
    {} // [GET] /users/{slug}

    public function cpostAction()
    {} // [POST] /users

    public function cpatchAction()
    {} // [PATCH] /users

    public function putAction($slug)
    {} // [PUT] /users/{slug}

    public function patchAction($slug)
    {} // [PATCH] /users/{slug}

    public function lockAction($slug)
    {} // [PATCH] /users/{slug}/lock

    public function getCommentsAction($slug)
    {} // [GET] /users/{slug}/comments

    public function getCommentAction($slug, $id)
    {} // [GET] /users/{slug}/comments/{id}

    public function deleteCommentAction($slug, $id)
    {} // [DELETE] /users/{slug}/comments/{id}

    public function banAction($slug, $id)
    {} // [PATCH] /users/{slug}/ban

    public function postCommentVoteAction($slug, $id)
    {} // [POST] /users/{slug}/comments/{id}/vote

    public function _userbarAction()
    {} // NO route

    public function check_usernameAction()
    {} // [GET] /users/check_username

    // conventional HATEOAS actions below

    public function newAction()
    {
    } // [GET] /users/new

    public function editAction($slug)
    {} // [GET] /user/{slug}/edit

    public function removeAction($slug)
    {} // [GET] /user/{slug}/remove

    public function newCommentsAction($slug)
    {} // [GET] /users/{slug}/comments/new

    public function editCommentAction($slug, $id)
    {} // [GET] /users/{slug}/comments/{id}/edit

    public function removeCommentAction($slug, $id)
    {} // [GET] /users/{slug}/comments/{id}/remove

    public function hideCommentAction($userId, $commentId)
    {} // [PATCH] /users/{userId}/comments/{commentId}/hide

    // Parameter of type Request should be ignored
    public function getVotesAction(Request $request, $slug)
    {} // [GET] /users/{slug}/votes

    public function getVoteAction(Request $request, $slug, $id)
    {} // [GET] /users/{slug}/votes/{id}

    public function getFoosAction($slug, Request $request)
    {} // [GET] /users/{slug}/foos
}
