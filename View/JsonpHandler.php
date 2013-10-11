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

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

use FOS\RestBundle\Util\Codes;

/**
 * Implements a custom handler for JSONP leveraging the ViewHandler
 *
 * @author Lukas K. Smith <smith@pooteeweet.org>
 */
class JsonpHandler
{
    protected $callbackParam;
    protected $callbackFilter;

    public function __construct($callbackParam, $callbackFilter)
    {
        $this->callbackParam = $callbackParam;
        $this->callbackFilter = $callbackFilter;
    }

    protected function getCallback(Request $request)
    {
        $callback = $request->query->get($this->callbackParam);

        if ($this->callbackFilter && !preg_match($this->callbackFilter, $callback)) {
            $msg = "Callback '$callback' does not match '{$this->callbackFilter}'";
            throw new HttpException(Codes::HTTP_BAD_REQUEST, $msg);
        }

        return $callback;
    }

    /**
     * Handles wrapping a JSON response into a JSONP response
     *
     * @param ViewHandler $handler
     * @param View    $view
     * @param Request $request
     * @param string  $format
     *
     * @return Response
     */
    public function createResponse(ViewHandler $handler, View $view, Request $request, $format)
    {
        $response = $handler->createResponse($view, $request, 'json');

        if ($response->isSuccessful()) {
            $callback = $this->getCallback($request);
            $response->setContent($callback.'('.$response->getContent().')');
            $response->headers->set('Content-Type', $request->getMimeType($format));
        }

        return $response;
    }
}
