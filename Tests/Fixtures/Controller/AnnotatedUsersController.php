<?php

namespace FOS\RestBundle\Tests\Fixtures\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/*
 * This file is part of the FOS/RestBundle
 *
 * (c) Lukas Kahwe Smith <smith@pooteeweet.org>
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 * (c) Bulat Shakirzyanov <mallluhuct@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class AnnotatedUsersController extends Controller
{
    public function getUsersAction()
    {} // [GET]     /users

    /**
     * @rest:Route(requirements={"slug" = "[a-z]+"})
     */
    public function getUserAction($slug)
    {} // [GET]     /users/{slug}

    /**
     * @rest:Route(requirements={"slug" = "[a-z]+", "id" = "\d+"})
     */
    public function getUserCommentAction($slug, $id)
    {} // [GET]     /users/{slug}/comments/{id}

    /**
     * @rest:Post(requirements={"slug" = "[a-z]+"})
     */
    public function rateUserAction($slug)
    {} // [POST]    /users/{slug}/rate

    /**
     * @rest:Route("/users/{slug}/rate_comment/{id}", requirements={"slug" = "[a-z]+", "id" = "\d+"})
     */
    public function rateUserCommentAction($slug, $id)
    {} // [PUT]     /users/{slug}/rate_comment/{id}

    /**
     * @rest:Get
     */
    public function cgetUserAction($slug)
    {} // [GET]     /users/{slug}/cget

    /**
     * @rest:Post
     */
    public function cpostUserAction($slug)
    {} // [POST]    /users/{slug}/cpost

    /**
     * @rest:Put
     */
    public function cputUserAction($slug)
    {} // [PUT]     /users/{slug}/cput

    /**
     * @rest:Delete
     */
    public function cdelUserAction($slug)
    {} // [DELETE]  /users/{slug}/cdel

    /**
     * @rest:Head
     */
    public function cheadUserAction($slug)
    {} // [HEAD]    /users/{slug}/chead


    /**
     * @rest:NoRoute
     */
    public function splitUserAction($slug)
    {}
}
