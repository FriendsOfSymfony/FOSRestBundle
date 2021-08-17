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
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller to test native PHP8 Route attributes.
 */
#[Rest\Route('/products')]
class RouteAttributesController extends AbstractFOSRestController
{
    /**
     * @return View view instance
     *
     * @Rest\View()
     */
    #[Rest\Get(path: '/{page}', name: 'product_list', requirements: ['page' => '\d+'], defaults: ['_format' => 'json'])]
    public function listAction(int $page)
    {
        $view = $this->view([
            ['name' => 'product1'],
            ['name' => 'product2'],
        ]);

        return $view;
    }

    /**
     * @return View view instance
     *
     * @Rest\View()
     */
    #[Rest\Post(path: '', name: 'product_create')]
    public function createAction(Request $request)
    {
        $view = $this->view([
            'name' => 'product1',
        ], 201);

        return $view;
    }
}
