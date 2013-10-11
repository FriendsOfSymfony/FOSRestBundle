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

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\Util\FormatNegotiatorInterface;

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
     * Initialize FormatListener.
     *
     * @param FormatNegotiatorInterface $formatNegotiator  The content negotiator service to use
     */
    public function __construct(FormatNegotiatorInterface $formatNegotiator)
    {
        $this->formatNegotiator = $formatNegotiator;
    }

    /**
     * Determines and sets the Request format
     *
     * @param GetResponseEvent $event The event
     *
     * @throws HttpException
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $format = $this->formatNegotiator->getBestFormat($request);

        if (null === $format) {
            if ($event->getRequestType() === HttpKernelInterface::MASTER_REQUEST) {
                throw new HttpException(Codes::HTTP_NOT_ACCEPTABLE, "No matching accepted Response format could be determined");
            }

            return;
        }

        $request->setRequestFormat($format);
    }
}
