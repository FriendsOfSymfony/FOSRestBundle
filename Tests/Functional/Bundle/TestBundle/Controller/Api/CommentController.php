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
    public function loginAction(): \Symfony\Component\HttpFoundation\JsonResponse
    {
        return new JsonResponse('login');
    }

    public function getCommentAction($id): \Symfony\Component\HttpFoundation\JsonResponse
    {
        return new JsonResponse(['id' => (int) $id]);
    }

    public function getComments(): \Symfony\Component\HttpFoundation\JsonResponse
    {
        return new JsonResponse([]);
    }
}
