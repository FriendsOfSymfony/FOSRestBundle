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
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\PropFind;
use FOS\RestBundle\Controller\Annotations\PropPatch;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\Patch;
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

 // [OPTIONS]     /users

    /**
     * @Copy()
     */
    public function copyUserAction($id)
    {
    }

 // [COPY] /users/{id}

    /**
     * @PropFind()
     */
    public function propfindUserPropsAction($id, $property)
    {
    }

 // [PROPFIND] /users/{id}/props/{property}

    /**
     * @PropPatch()
     */
    public function proppatchUserPropsAction($id, $property)
    {
    }

 // [PROPPATCH] /users/{id}/props/{property}

    /**
     * @Move()
     */
    public function moveUserAction($id)
    {
    }

 // [MOVE] /users/{id}

    /**
     * @Mkcol()
     */
    public function mkcolUsersAction()
    {
    }

 // [MKCOL] /users

    /**
     * @Lock()
     */
    public function lockUserAction($slug)
    {
    }

 // [LOCK] /users/{slug}

    /**
     * @Unlock()
     */
    public function unlockUserAction($slug)
    {
    }

 // [UNLOCK] /users/{slug}

    public function boptionsUsersAction()
    {
    }

 // [OPTIONS]     /users

    public function getUsersAction()
    {
    }

 // [GET]     /users

    /**
     * @Route(requirements={"slug" = "[a-z]+"})
     */
    public function getUserAction($slug)
    {
    }

 // [GET]     /users/{slug}

    /**
     * @Route(requirements={"slug" = "[a-z]+", "id" = "\d+"}, options={"expose"=true})
     */
    public function getUserPostAction($slug, $id)
    {
    }

 // [GET]     /users/{slug}/posts/{id}

    /**
     * @Patch
     */
    public function patchUsersAction()
    {
    }

    /**
     * @Patch(requirements={"slug" = "[a-z]+"})
     */
    public function patchUserAction($slug)
    {
    }

 // [GET]     /users/{slug}

    /**
     * @Route(requirements={"slug" = "[a-z]+", "id" = "\d+"})
     */
    public function getUserCommentAction($slug, $id)
    {
    }

 // [GET]     /users/{slug}/comments/{id}

    /**
     * @Post(requirements={"slug" = "[a-z]+"})
     */
    public function rateUserAction($slug)
    {
    }

 // [POST]    /users/{slug}/rate

    /**
     * @Route("/users/{slug}/rate_comment/{id}", requirements={"slug" = "[a-z]+", "id" = "\d+"}, methods={"PATCH", "POST"})
     */
    public function rateUserCommentAction($slug, $id)
    {
    }

 // [PATCH, POST]     /users/{slug}/rate_comment/{id}

    /**
     * @Get
     */
    public function bgetUserAction($slug)
    {
    }

 // [GET]     /users/{slug}/bget

    /**
     * @Post
     */
    public function bpostUserAction($slug)
    {
    }

 // [POST]    /users/{slug}/bpost

    /**
     * @Put
     */
    public function bputUserAction($slug)
    {
    }

 // [PUT]     /users/{slug}/bput

    /**
     * @Delete
     */
    public function bdelUserAction($slug)
    {
    }

 // [DELETE]  /users/{slug}/bdel

    /**
     * @Head
     */
    public function bheadUserAction($slug)
    {
    }

 // [HEAD]    /users/{slug}/bhead

    /**
     * @Link
     */
    public function bLinkUserAction($slug)
    {
    }

 // [LINK]    /users/{slug}/blink

    /**
     * @Unlink
     */
    public function bunlinkUserAction($slug)
    {
    }

 // [UNLINK]    /users/{slug}/bunlink

    /**
     * @NoRoute
     */
    public function splitUserAction($slug)
    {
    }

    /**
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
