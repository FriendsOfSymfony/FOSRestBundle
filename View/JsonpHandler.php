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
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Implements a custom handler for JSONP leveraging the ViewHandler.
 *
 * @author Lukas K. Smith <smith@pooteeweet.org>
 */
final class JsonpHandler
{
    private $callbackParam;

    public function __construct(string $callbackParam)
    {
        $this->callbackParam = $callbackParam;
    }

    /**
     * Handles wrapping a JSON response into a JSONP response.
     */
    public function createResponse(ViewHandler $handler, View $view, Request $request, string $format): Response
    {
        $response = $handler->createResponse($view, $request, 'json');

        if ($response->isSuccessful()) {
            $callback = $this->getCallback($request);
            $response->setContent(sprintf('/**/%s(%s)', $callback, $response->getContent()));
            $response->headers->set('Content-Type', $request->getMimeType($format));
        }

        return $response;
    }

    private function getCallback(Request $request): string
    {
        $callback = $request->query->get($this->callbackParam);
        $validator = new \JsonpCallbackValidator();

        if (!is_string($callback) || !$validator->validate($callback)) {
            throw new BadRequestHttpException('Invalid JSONP callback value');
        }

        return $callback;
    }
}
