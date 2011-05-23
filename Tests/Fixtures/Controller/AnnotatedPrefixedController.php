<?php

namespace FOS\RestBundle\Tests\Fixtures\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use FOS\RestBundle\Controller\Annotations\Prefix;

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

/**
 * @author Donald Tyler <chekote69@gmail.com>
 * @Prefix("aprefix")
 */
class AnnotatedPrefixedController extends Controller
{
    public function getSomethingAction()
    {} // [GET]     /aprefix/something.{_format}
}