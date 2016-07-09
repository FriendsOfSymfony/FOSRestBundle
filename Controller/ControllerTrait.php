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
    /**
     * @var ViewHandlerInterface
     */
    private $viewhandler;

    /**
     * Get the ViewHandler.
     *
     * @param ViewHandlerInterface $viewhandler
     */
    public function setViewHandler(ViewHandlerInterface $viewhandler)
    {
        $this->viewhandler = $viewhandler;
    }

    /**
     * Get the ViewHandler.
     *
     * @return ViewHandlerInterface
     */
    protected function getViewHandler()
    {
        if (!$this->viewhandler instanceof ViewHandlerInterface) {
            throw new \RuntimeException('A "ViewHandlerInterface" instance must be set when using the FOSRestBundle "ControllerTrait".');
        }

        return $this->viewhandler;
    }

    /**
     * Creates a view.
     *
     * Convenience method to allow for a fluent interface.
     *
     * @param mixed $data
     * @param int   $statusCode
     * @param array $headers
     *
     * @return View
     */
    protected function view($data = null, $statusCode = null, array $headers = [])
    {
        return View::create($data, $statusCode, $headers);
    }

    /**
     * Creates a Redirect view.
     *
     * Convenience method to allow for a fluent interface.
     *
     * @param string $url
     * @param int    $statusCode
     * @param array  $headers
     *
     * @return View
     */
    protected function redirectView($url, $statusCode = Response::HTTP_FOUND, array $headers = [])
    {
        return View::createRedirect($url, $statusCode, $headers);
    }

    /**
     * Creates a Route Redirect View.
     *
     * Convenience method to allow for a fluent interface.
     *
     * @param string $route
     * @param mixed  $parameters
     * @param int    $statusCode
     * @param array  $headers
     *
     * @return View
     */
    protected function routeRedirectView($route, array $parameters = [], $statusCode = Response::HTTP_CREATED, array $headers = [])
    {
        return View::createRouteRedirect($route, $parameters, $statusCode, $headers);
    }

    /**
     * Converts view into a response object.
     *
     * Not necessary to use, if you are using the "ViewResponseListener", which
     * does this conversion automatically in kernel event "onKernelView".
     *
     * @param View $view
     *
     * @return Response
     */
    protected function handleView(View $view)
    {
        return $this->getViewHandler()->handle($view);
    }
}
