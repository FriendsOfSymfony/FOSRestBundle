<?php

namespace FOS\RestBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent,
    Symfony\Component\Serializer\SerializerInterface,
    Symfony\Component\HttpKernel\Exception\HttpException,
    Symfony\Component\HttpKernel\HttpKernelInterface;

use FOS\RestBundle\Response\Codes;
use FOS\RestBundle\Request\ContentNegotiatorInterface;

/*
 * This file is part of the FOSRestBundle
 *
 * (c) Lukas Kahwe Smith <smith@pooteeweet.org>
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 * (c) Bulat Shakirzyanov <avalanche123>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * This listener handles Accept header format negotiations.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class FormatListener
{
    /**
     * @var     array   Ordered array of formats (highest priority first)
     */
    protected $defaultPriorities;

    /**
     * @var     string  fallback format name
     */
    protected $fallbackFormat;

    private $contentNegotiator;

    /**
     * Initialize FormatListener.
     *
     * @param   string  $fallbackFormat     Default fallback format
     * @param   array   $defaultPriorities  Ordered array of formats (highest priority first)
     */
    public function __construct(ContentNegotiatorInterface $contentNegotiator, $fallbackFormat, array $defaultPriorities = array())
    {
        $this->contentNegotiator = $contentNegotiator;
        $this->defaultPriorities = $defaultPriorities;
        $this->fallbackFormat = $fallbackFormat;
    }

    /**
     * Determines and sets the Request format
     *
     * @param   GetResponseEvent   $event    The event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();

/*
        // TODO get priorities from the controller action
        $action = $request->attributes->get('_controller');
        $controller = $event->getController();
        $priorities =
*/
        if (empty($priorities)) {
            $priorities = $this->defaultPriorities;
        }

        $format = null;
        if (!empty($priorities)) {
            $format = $this->contentNegotiator->getBestMediaType($request, $priorities);
        }

        if (null === $format) {
            $format = $this->fallbackFormat;
        }

        if (null === $format) {
            if ($event->getRequestType() === HttpKernelInterface::MASTER_REQUEST)  {
                throw new HttpException(Codes::HTTP_NOT_ACCEPTABLE, "No matching accepted Response format could be determined");
            }

            return;
        }

        $request->setRequestFormat($format);
    }
}
