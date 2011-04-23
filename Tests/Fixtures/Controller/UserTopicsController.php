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

class UserTopicsController extends Controller
{
    public function getTopicsAction($slug)
    {} // [GET] /users/{slug}/topics

    public function newTopicsAction($slug)
    {} // [GET] /users/{slug}/topics/new

    public function getTopicAction($slug, $title)
    {} // [GET] /users/{slug}/topics/{title}

    public function putTopicAction($slug, $title)
    {} // [PUT] /users/{slug}/topics/{title}

    public function hideTopicAction($slug, $title)
    {} // [PUT] /users/{slug}/topics/{title}/hide
}
