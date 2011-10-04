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

use FOS\RestBundle\Controller\Annotations\Route,
    FOS\RestBundle\Controller\Annotations\NoRoute,
    FOS\RestBundle\Controller\Annotations\Get,
    FOS\RestBundle\Controller\Annotations\Post,
    FOS\RestBundle\Controller\Annotations\Put,
    FOS\RestBundle\Controller\Annotations\Patch,
    FOS\RestBundle\Controller\Annotations\Delete,
    FOS\RestBundle\Controller\Annotations\Head;

class AnnotatedUsersController extends Controller
{
    public function getUsersAction()
    {} // [GET]     /users

    /**
     * @Route(requirements={"slug" = "[a-z]+"})
     */
    public function getUserAction($slug)
    {} // [GET]     /users/{slug}

    /**
     * @Patch
     */
    public function patchUsersAction()
    {}

    /**
     * @Patch(requirements={"slug" = "[a-z]+"})
     */
    public function patchUserAction($slug)
    {} // [GET]     /users/{slug}

    /**
     * @Route(requirements={"slug" = "[a-z]+", "id" = "\d+"})
     */
    public function getUserCommentAction($slug, $id)
    {} // [GET]     /users/{slug}/comments/{id}

    /**
     * @Post(requirements={"slug" = "[a-z]+"})
     */
    public function rateUserAction($slug)
    {} // [POST]    /users/{slug}/rate

    /**
     * @Route("/users/{slug}/rate_comment/{id}", requirements={"slug" = "[a-z]+", "id" = "\d+"})
     */
    public function rateUserCommentAction($slug, $id)
    {} // [POST]     /users/{slug}/rate_comment/{id}

    /**
     * @Get
     */
    public function cgetUserAction($slug)
    {} // [GET]     /users/{slug}/cget

    /**
     * @Post
     */
    public function cpostUserAction($slug)
    {} // [POST]    /users/{slug}/cpost

    /**
     * @Put
     */
    public function cputUserAction($slug)
    {} // [PUT]     /users/{slug}/cput

    /**
     * @Delete
     */
    public function cdelUserAction($slug)
    {} // [DELETE]  /users/{slug}/cdel

    /**
     * @Head
     */
    public function cheadUserAction($slug)
    {} // [HEAD]    /users/{slug}/chead


    /**
     * @NoRoute
     */
    public function splitUserAction($slug)
    {}
}
