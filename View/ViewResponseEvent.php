<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\View;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Allows to update the Response an the basis of view data.
 */
class ViewResponseEvent
{
    private $view;
    private $response;
    private $request;

    public function __construct(View $view, Response $response, Request $request)
    {
        $this->view = $view;
        $this->response = $response;
        $this->request = $request;
    }

    public function getView(): View
    {
        return $this->view;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }
}
