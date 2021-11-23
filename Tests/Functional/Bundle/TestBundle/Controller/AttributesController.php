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
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller to test native PHP8 attributes.
 */
#[Rest\Route('/products')]
class AttributesController extends AbstractFOSRestController
{
    #[Rest\Get(path: '/{page}', name: 'product_list', requirements: ['page' => '\d+'], defaults: ['_format' => 'json'])]
    #[Rest\View]
    public function listAction(int $page)
    {
        return [
            ['name' => 'product1'],
            ['name' => 'product2'],
        ];
    }

    #[Rest\Post(path: '', name: 'product_create')]
    #[Rest\View(statusCode: 201)]
    public function createAction(Request $request)
    {
        return [
            'name' => 'product1',
        ];
    }
}
