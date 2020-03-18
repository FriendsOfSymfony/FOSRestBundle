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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * This listener handles registering custom mime types.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 *
 * @internal
 */
class MimeTypeListener
{
    private $mimeTypes;

    /**
     * @param array $mimeTypes An array with the format as key and
     *                         the corresponding mime type as value
     */
    public function __construct(array $mimeTypes)
    {
        $this->mimeTypes = $mimeTypes;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$request->attributes->get(FOSRestBundle::ZONE_ATTRIBUTE, true)) {
            return;
        }

        if (HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            foreach ($this->mimeTypes as $format => $mimeTypes) {
                $mimeTypes = array_merge($mimeTypes, Request::getMimeTypes($format));

                $request->setFormat($format, $mimeTypes);
            }
        }
    }
}
