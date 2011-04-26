<?php

namespace FOS\RestBundle\Tests\Fixtures\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/*
 * This file is part of the FOSRestBundle
 *
 * (c) Lukas Kahwe Smith <smith@pooteeweet.org>
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 * (c) Bulat Shakirzyanov <mallluhuct@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class UsersController extends Controller
{
    public function getUsersAction()
    {} // [GET] /users

    public function getUserAction($slug)
    {} // [GET] /users/{slug}

    public function postUsersAction()
    {} // [POST] /users

    public function putUserAction($slug)
    {} // [PUT] /users/{slug}

    public function lockUserAction($slug)
    {} // [PUT] /users/{slug}/lock

    public function getUserCommentsAction($slug)
    {} // [GET] /users/{slug}/comments

    public function getUserCommentAction($slug, $id)
    {} // [GET] /users/{slug}/comments/{id}

    public function deleteUserCommentAction($slug, $id)
    {} // [DELETE] /users/{slug}/comments/{id}

    public function newUsersAction()
    {} // [GET] /users/new

    public function newUserCommentsAction($slug)
    {} // [GET] /users/{slug}/comments/new

    public function banUserAction($slug, $id)
    {} // [PUT] /users/{slug}/ban

    public function voteUserCommentAction($slug, $id)
    {} // [PUT] /users/{slug}/comments/{id}/vote

    public function _userbarAction()
    {} // NO route

    public function check_usernameUsersAction()
    {} // [GET] /users/check_username
}
