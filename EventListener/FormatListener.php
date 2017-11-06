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

use FOS\RestBundle\FOSRestBundle;
use FOS\RestBundle\Util\StopFormatListenerException;
use FOS\RestBundle\Negotiation\FormatNegotiator;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * This listener handles Accept header format negotiations.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 *
 * @internal
 */
class FormatListener
{
    private $formatNegotiator;

    /**
     * Initialize FormatListener.
     *
     * @param FormatNegotiatorInterface $formatNegotiator
     */
    public function __construct(FormatNegotiator $formatNegotiator)
    {
        $this->formatNegotiator = $formatNegotiator;
    }

    /**
     * Determines and sets the Request format.
     *
     * @param GetResponseEvent $event The event
     *
     * @throws NotAcceptableHttpException
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->get(FOSRestBundle::ZONE_ATTRIBUTE, true)) {
            return;
        }

        try {
            $format = $request->getRequestFormat(null);
            if (null === $format) {
                $accept = $this->formatNegotiator->getBest('');
                if (null !== $accept && 0.0 < $accept->getQuality()) {
                    $format = $request->getFormat($accept->getValue());
                    if (null !== $format) {
                        $request->attributes->set('media_type', $accept->getValue());
                    }
                }
            }

            if (null === $format) {
                if (HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
                    throw new NotAcceptableHttpException('No matching accepted Response format could be determined');
                }

                return;
            }

            $request->setRequestFormat($format);
        } catch (StopFormatListenerException $e) {
            // nothing to do
        }
    }
}
