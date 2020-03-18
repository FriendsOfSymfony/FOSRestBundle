<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Controller;

use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Trait for Controllers using the View functionality of FOSRestBundle.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
trait ControllerTrait
{
    private $viewhandler;

    public function setViewHandler(ViewHandlerInterface $viewhandler)
    {
        $this->viewhandler = $viewhandler;
    }

    protected function getViewHandler()
    {
        if (!$this->viewhandler instanceof ViewHandlerInterface) {
            throw new \RuntimeException('A "ViewHandlerInterface" instance must be set when using the FOSRestBundle "ControllerTrait".');
        }

        return $this->viewhandler;
    }

    /**
     * @return View
     */
    protected function view($data = null, ?int $statusCode = null, array $headers = [])
    {
        return View::create($data, $statusCode, $headers);
    }

    /**
     * @return View
     */
    protected function redirectView(string $url, int $statusCode = Response::HTTP_FOUND, array $headers = [])
    {
        return View::createRedirect($url, $statusCode, $headers);
    }

    /**
     * @return View
     */
    protected function routeRedirectView(string $route, array $parameters = [], int $statusCode = Response::HTTP_CREATED, array $headers = [])
    {
        return View::createRouteRedirect($route, $parameters, $statusCode, $headers);
    }

    /**
     * Converts view into a response object.
     *
     * Not necessary to use, if you are using the "ViewResponseListener", which
     * does this conversion automatically in kernel event "onKernelView".
     *
     * @return Response
     */
    protected function handleView(View $view)
    {
        return $this->getViewHandler()->handle($view);
    }
}
