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
    Symfony\Component\HttpKernel\Exception\HttpException,
    Symfony\Component\HttpKernel\HttpKernelInterface;

use FOS\Rest\Util\Codes,
    FOS\Rest\Util\FormatNegotiatorInterface;

/**
 * This listener handles Accept header format negotiations.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class FormatListener
{
    /**
     * @var FormatNegotiatorInterface
     */
    private $formatNegotiator;

    /**
     * @var array   Ordered array of formats (highest priority first)
     */
    private $defaultPriorities;

    /**
     * @var string  fallback format name
     */
    private $fallbackFormat;

    /**
     * @var Boolean if to consider the extension last or first
     */
    private $preferExtension;

    /**
     * Initialize FormatListener.
     *
     * @param   FormatNegotiatorInterface   $formatNegotiator  The content negotiator service to use
     * @param   string  $fallbackFormat     Default fallback format
     * @param   array   $defaultPriorities  Ordered array of formats (highest priority first)
     * @param   Boolean $preferExtension    If to consider the extension last or first
     */
    public function __construct(FormatNegotiatorInterface $formatNegotiator, $fallbackFormat, array $defaultPriorities = array(), $preferExtension = false)
    {
        $this->formatNegotiator = $formatNegotiator;
        $this->defaultPriorities = $defaultPriorities;
        $this->fallbackFormat = $fallbackFormat;
        $this->preferExtension = $preferExtension;
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
            $format = $this->formatNegotiator->getBestFormat($request, $priorities, $this->preferExtension);
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
