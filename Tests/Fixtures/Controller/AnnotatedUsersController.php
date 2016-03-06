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

use FOS\RestBundle\Controller\Annotations\Copy;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Head;
use FOS\RestBundle\Controller\Annotations\Link;
use FOS\RestBundle\Controller\Annotations\Lock;
use FOS\RestBundle\Controller\Annotations\Mkcol;
use FOS\RestBundle\Controller\Annotations\Move;
use FOS\RestBundle\Controller\Annotations\NoRoute;
use FOS\RestBundle\Controller\Annotations\Options;
use FOS\RestBundle\Controller\Annotations\Patch;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\PropFind;
use FOS\RestBundle\Controller\Annotations\PropPatch;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\Annotations\Unlink;
use FOS\RestBundle\Controller\Annotations\Unlock;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AnnotatedUsersController extends Controller
{
    /**
     * @Options
     */
    public function optionsUsersAction()
    {
    }

    /**
     * [COPY] /users/{id}.
     *
     * @param $id
     *
     * @Copy()
     */
    public function copyUserAction($id)
    {
    }

    /**
     * [PROPFIND] /users/{id}/props/{property}.
     *
     * @param $id
     * @param $property
     *
     * @PropFind()
     */
    public function propfindUserPropsAction($id, $property)
    {
    }

    /**
     * @PropPatch()
     */
    public function proppatchUserPropsAction($id, $property)
    {
    }

    /**
     * @Move()
     */
    public function moveUserAction($id)
    {
    }

    /**
     * @Mkcol()
     */
    public function mkcolUsersAction()
    {
    }

    /**
     * @Lock()
     */
    public function lockUserAction($slug)
    {
    }

    /**
     * @Unlock()
     */
    public function unlockUserAction($slug)
    {
    }

    public function boptionsUsersAction()
    {
    }

    /**
     * [GET]     /users.
     */
    public function getUsersAction()
    {
    }

    /**
     * [GET]     /users/{slug}.
     *
     * @Route(requirements={"slug" = "[a-z]+"})
     */
    public function getUserAction($slug)
    {
    }

    /**
     * [GET]     /users/{slug}/posts/{id}.
     *
     * @Route(requirements={"slug" = "[a-z]+", "id" = "\d+"}, options={"expose"=true})
     */
    public function getUserPostAction($slug, $id)
    {
    }

    /**
     * [PATCH]     /users.
     *
     * @Patch
     */
    public function patchUsersAction()
    {
    }

    /**
     * [PATCH]     /users/{slug}.
     *
     * @Patch(requirements={"slug" = "[a-z]+"})
     */
    public function patchUserAction($slug)
    {
    }

    /**
     * [GET]     /users/{slug}/comments/{id}.
     *
     * @Route(requirements={"slug" = "[a-z]+", "id" = "\d+"})
     */
    public function getUserCommentAction($slug, $id)
    {
    }

    /**
     * [POST]    /users/{slug}/rate.
     *
     * @Post(requirements={"slug" = "[a-z]+"})
     */
    public function rateUserAction($slug)
    {
    }

    /**
     * [PATCH, POST]     /users/{slug}/rate_comment/{id}.
     *
     * @Route("/users/{slug}/rate_comment/{id}", requirements={"slug" = "[a-z]+", "id" = "\d+"}, methods={"PATCH", "POST"})
     */
    public function rateUserCommentAction($slug, $id)
    {
    }

    /**
     * [GET]     /users/{slug}/bget.
     *
     * @Get
     */
    public function bgetUserAction($slug)
    {
    }

    /**
     * [POST]    /users/{slug}/bpost.
     *
     * @Post
     */
    public function bpostUserAction($slug)
    {
    }

    /**
     * [PUT]     /users/{slug}/bput.
     *
     * @Put
     */
    public function bputUserAction($slug)
    {
    }

    /**
     * [DELETE]  /users/{slug}/bdel.
     *
     * @Delete
     */
    public function bdelUserAction($slug)
    {
    }

    /**
     * [HEAD]    /users/{slug}/bhead.
     *
     * @Head
     */
    public function bheadUserAction($slug)
    {
    }

    /**
     * [LINK]    /users/{slug}/blink.
     *
     * @Link
     */
    public function bLinkUserAction($slug)
    {
    }

    /**
     * [UNLINK]    /users/{slug}/bunlink.
     *
     * @Unlink
     */
    public function bunlinkUserAction($slug)
    {
    }

    /**
     * @NoRoute
     */
    public function splitUserAction($slug)
    {
    }

    /**
     * [GET]    /users/{slug}/custom.
     *
     * @Route(requirements={"_format"="custom"})
     */
    public function customUserAction($slug)
    {
    }

    /**
     * @Link("/users1", name="_a_link_method")
     * @Get("/users2",  name="_a_get_method")
     * @Get("/users3",  name="_an_other_get_method")
     * @Post("/users4",  name="_a_post_method")
     */
    public function multiplegetUsersAction()
    {
    }

    /**
     * @POST("/users1/{foo}", name="post_users_foo", options={ "method_prefix" = false })
     * @POST("/users2/{foo}", name="post_users_bar", options={ "method_prefix" = false })
     */
    public function multiplepostUsersAction()
    {
    }
}
