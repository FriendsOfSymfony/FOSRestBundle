<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Functional\Bundle\TestBundle\Controller;

use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Version;
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @author Ener-Getick <egetick@gmail.com>
 *
 * @Version({"1.2"})
 */
class Version2Controller
{
    /**
     * @View("TestBundle:Version:version.html.twig")
     * @Get(path="/version")
     */
    public function versionAction($version)
    {
        return array('version' => 'test annotation');
    }
}
