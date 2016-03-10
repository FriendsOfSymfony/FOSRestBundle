<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Functional\Bundle\TestBundle\Controller\Api;

use Symfony\Component\HttpFoundation\JsonResponse;

class CommentController
{
    public function getCommentAction($id)
    {
        return new JsonResponse(array('id' => $id));
    }
}
