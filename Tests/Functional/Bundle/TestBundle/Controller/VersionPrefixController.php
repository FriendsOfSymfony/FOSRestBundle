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

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Version;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Version({"1.2"})
 */
class VersionPrefixController extends AbstractFOSRestController
{
    /**
     * @Get("/version")
     */
    public function versionAction($version)
    {
        return new JsonResponse(array('version' => $version));
    }
}
