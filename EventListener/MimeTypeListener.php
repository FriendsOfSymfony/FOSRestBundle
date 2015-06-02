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

use FOS\RestBundle\Util\FormatNegotiator;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * This listener handles registering custom mime types.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class MimeTypeListener
{
    private $mimeTypes;
    private $formatNegotiator;

    /**
     * Constructor.
     *
     * @param array            $mimeTypes        An array with the format as key and
     *                                           the corresponding mime type as value
     * @param FormatNegotiator $formatNegotiator
     */
    public function __construct(array $mimeTypes, FormatNegotiator $formatNegotiator)
    {
        $this->mimeTypes = $mimeTypes;
        $this->formatNegotiator = $formatNegotiator;
    }

    /**
     * Core request handler
     *
     * @param GetResponseEvent $event The event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            foreach ($this->mimeTypes['formats'] as $format => $mimeType) {
                $request->setFormat($format, $mimeType);
                $this->formatNegotiator->registerFormat($format, (array) $mimeType, true);
            }
        }
    }
}
