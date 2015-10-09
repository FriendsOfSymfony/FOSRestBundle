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
use FOS\RestBundle\Controller\Annotations\Prefix;

/**
 * @author Donald Tyler <chekote69@gmail.com>
 * @Prefix("aprefix")
 */
class AnnotatedPrefixedController extends Controller
{
    public function getSomethingAction()
    {
    }

 // [GET]     /aprefix/something.{_format}
}
