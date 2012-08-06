<?php

namespace FOS\RestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use FOS\RestBundle\View\View;
use FOS\RestBundle\View\RedirectView;
use FOS\Rest\Util\Codes;

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
    protected function view($data = null, $statusCode = null, array $headers = array(), $templateVar = 'data')
    {
        return View::create($data, $statusCode, $headers)
            ->setTemplateVar($templateVar)
        ;
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
     * @param mixed   $data
     * @param integer $statusCode
     * @param array   $headers
     *
     * @return View
     */
    protected function routeRedirectView($route, array $data = array(), $statusCode = Codes::HTTP_CREATED, array $headers = array())
    {
        return RouteRedirectView::create($route, $data, $statusCode, $headers);
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

