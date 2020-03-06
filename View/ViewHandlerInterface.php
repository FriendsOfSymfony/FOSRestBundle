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
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author Lukas K. Smith <smith@pooteeweet.org>
 */
interface ViewHandlerInterface
{
    /**
     * @param string $format
     *
     * @return bool
     */
    public function supports($format);

    /**
     * Registers a custom handler.
     *
     * The handler must have the following signature: handler($viewObject, $request, $response)
     * It can use the methods of this class to retrieve the needed data and return a
     * Response object ready to be sent.
     *
     * @param string   $format
     * @param callable $callable
     */
    public function registerHandler($format, $callable);

    /**
     * If the given format uses the templating system for rendering.
     *
     * @deprecated since 2.8
     *
     * @param string $format
     *
     * @return bool
     */
    public function isFormatTemplating($format);

    /**
     * Handles a request with the proper handler.
     *
     * Decides on which handler to use based on the request format
     *
     * @return Response
     */
    public function handle(View $view, Request $request = null);

    /**
     * @param string $location
     * @param string $format
     *
     * @return Response
     */
    public function createRedirectResponse(View $view, $location, $format);

    /**
     * @deprecated since 2.8
     *
     * @param string $format
     *
     * @return string
     */
    public function renderTemplate(View $view, $format);

    /**
     * @deprecated since 2.8
     *
     * @return array
     */
    public function prepareTemplateParameters(View $view);

    /**
     * @param string $format
     *
     * @return Response
     */
    public function createResponse(View $view, Request $request, $format);
}
