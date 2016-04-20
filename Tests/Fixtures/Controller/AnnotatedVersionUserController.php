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

use FOS\RestBundle\Controller\Annotations\Version;
use FOS\RestBundle\Controller\Annotations\Get;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Version({"v1", "v3"})
 */
class AnnotatedVersionUserController extends Controller
{
    /**
     * [GET, HEAD]     /users/{slug}/v2.
     *
     * @Get()
     */
    public function v1UserAction()
    {
    }

    /**
     * [GET, HEAD]     /users/{slug}/conditional.
     *
     * @Get(condition="context.getMethod() in ['GET', 'HEAD'] and request.headers.get('User-Agent') matches '/firefox/i'")
     */
    public function conditionalUserAction()
    {
    }

    public function v3UserAction()
    {
    }
}
