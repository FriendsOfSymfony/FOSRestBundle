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

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;

class MediaController extends FOSRestController implements ClassResourceInterface
{
    /**
     * [GET] /media.
     */
    public function cgetAction()
    {
    }

    /**
     * [GET] /media/{slug}.
     *
     * @param $slug
     */
    public function getAction($slug)
    {
    }
}
