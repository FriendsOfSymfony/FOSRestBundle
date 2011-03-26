<?php

namespace FOS\RestBundle\Tests\Fixtures\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/*
 * This file is part of the FOS/RestBundle
 *
 * (c) Lukas Kahwe Smith <smith@pooteeweet.org>
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 * (c) Bulat Shakirzyanov <avalanche123>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class UserTopicCommentsController extends Controller
{
    public function getCommentsAction($slug, $title)
    {} // [GET] /users/{slug}/topics/{title}/comments

    public function newCommentsAction($slug, $title)
    {} // [GET] /users/{slug}/topics/{title}/comments/new

    public function putCommentAction($slug, $title, $id)
    {} // [PUT] /users/{slug}/topics/{title}/comments/{id}

    public function banCommentAction($slug, $title, $id)
    {} // [PUT] /users/{slug}/topics/{title}/comments/{id}/ban
}
