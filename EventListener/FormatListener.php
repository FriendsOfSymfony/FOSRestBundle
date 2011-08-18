<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent,
    Symfony\Component\Serializer\SerializerInterface,
    Symfony\Component\HttpKernel\Exception\HttpException,
    Symfony\Component\HttpKernel\HttpKernelInterface;

use FOS\RestBundle\Response\Codes,
    FOS\RestBundle\Request\ContentNegotiatorInterface;

/**
 * This listener handles Accept header format negotiations.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class FormatListener
{
    /**
     * @var ContentNegotiatorInterface
     */
    private $contentNegotiator;

    /**
     * @var array   Ordered array of formats (highest priority first)
     */
    private $defaultPriorities;

    /**
     * @var string  fallback format name
     */
    private $fallbackFormat;

    /**
     * Initialize FormatListener.
     *
     * @param   ContentNegotiatorInterface  $contentNegotiator  The content negotiator service to use
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
