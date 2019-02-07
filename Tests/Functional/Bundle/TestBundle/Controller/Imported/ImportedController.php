<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Functional\Bundle\TestBundle\Controller\Imported;

use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Component\HttpFoundation\JsonResponse;

class ImportedController
{
    /**
     * @Post("/imported-with-trailing-slash/")
     * @Get("/imported-with-trailing-slash/")
     * @Post("/post-without-trailing-slash")
     * @Get("/imported-without-trailing-slash")
     */
    public function importedAction()
    {
        return new JsonResponse();
    }
}
