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
     * @return bool
     */
    public function supports(string $format);

    /**
     * Registers a custom handler.
     *
     * The handler must have the following signature: handler($viewObject, $request, $response)
     * It can use the methods of this class to retrieve the needed data and return a
     * Response object ready to be sent.
     */
    public function registerHandler(string $format, callable $callable);

    /**
     * Handles a request with the proper handler.
     *
     * Decides on which handler to use based on the request format
     *
     * @return Response
     */
    public function handle(View $view, Request $request = null);

    /**
     * @return Response
     */
    public function createRedirectResponse(View $view, string $location, string $format);

    /**
     * @return Response
     */
    public function createResponse(View $view, Request $request, string $format);
}
