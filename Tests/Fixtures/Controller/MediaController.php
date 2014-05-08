<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace FOS\RestBundle\Tests\Fixtures\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;

class MediaController extends FOSRestController implements ClassResourceInterface
{
    public function cgetAction()
    {} // [GET] /media

    public function getAction($slug)
    {} // [GET] /media/{slug}
} 
