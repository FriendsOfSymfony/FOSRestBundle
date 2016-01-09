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

use FOS\RestBundle\Controller\Annotations\View;

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
class VersionController
{
    /**
     * @View("TestBundle:Version:version.html.twig")
     */
    public function versionAction($version)
    {
        return array('version' => $version);
    }
}
