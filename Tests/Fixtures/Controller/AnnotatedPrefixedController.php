<?php

namespace FOS\RestBundle\Tests\Fixtures\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/*
 * This file is part of the FOSRestBundle
 *
 * (c) Donald Tyler <chekote69@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * @rest:Prefix("aprefix")
 */
class AnnotatedPrefixedController extends Controller
{
    public function getSomethingAction()
    {} // [GET]     /aprefix/something.{_format}
}