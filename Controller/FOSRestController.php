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

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\View\View;
use FOS\RestBundle\View\RedirectView;
use FOS\RestBundle\View\RouteRedirectView;
use FOS\RestBundle\Util\Codes;

/**
 * Base Controller for Controllers using the View functionality of FOSRestBundle.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
abstract class FOSRestController extends Controller
{
    /**
     * Create a view
     *
     * Convenience method to allow for a fluent interface.
     *
     * @param mixed   $data
     * @param integer $statusCode
     * @param array   $headers
     *
     * @return View
     */
    protected function view($data = null, $statusCode = null, array $headers = array())
    {
        return View::create($data, $statusCode, $headers);
    }

    /**
     * Create a Redirect view
     *
     * Convenience method to allow for a fluent interface.
     *
     * @param string  $url
     * @param integer $statusCode
     * @param array   $headers
     *
     * @return View
     */
    protected function redirectView($url, $statusCode = Codes::HTTP_FOUND, array $headers = array())
    {
        return RedirectView::create($url, $statusCode, $headers);
    }

    /**
     * Create a Route Redirect View
     *
     * Convenience method to allow for a fluent interface.
     *
     * @param string  $route
     * @param mixed   $parameters
     * @param integer $statusCode
     * @param array   $headers
     *
     * @return View
     */
    protected function routeRedirectView($route, array $parameters = array(), $statusCode = Codes::HTTP_CREATED, array $headers = array())
    {
        return RouteRedirectView::create($route, $parameters, $statusCode, $headers);
    }

    /**
     * Convert view into a response object.
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
        return $this->get('fos_rest.view_handler')->handle($view);
    }
}

