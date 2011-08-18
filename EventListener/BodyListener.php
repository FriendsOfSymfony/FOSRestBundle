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

use Symfony\Component\HttpFoundation\ParameterBag,
    Symfony\Component\HttpKernel\Event\GetResponseEvent,
    Symfony\Component\DependencyInjection\ContainerAware;

/**
 * This listener handles Request body decoding.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class BodyListener extends ContainerAware
{
    /**
     * @var array
     */
    protected $decoders;

    /**
     * Set a serializer instance
     *
     * @param   array $decoders List of key (format) value (service ids) of decoders
     */
    public function __construct(array $decoders)
    {
        $this->decoders = $decoders;
    }

    /**
     * Core request handler
     *
     * @param   GetResponseEvent   $event    The event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!count($request->request->all())
            && in_array($request->getMethod(), array('POST', 'PUT', 'PATCH', 'DELETE'))
        ) {
            $content_type = $request->headers->get('Content-Type');

            $format = null === $content_type
                ? $request->getRequestFormat()
                : $request->getFormat($request->headers->get('Content-Type'));

            if (null === $format || empty($this->decoders[$format])) {
                return;
            }

            $decoder = $this->container->get($this->decoders[$format]);

            $data = $decoder->decode($request->getContent(), $format);
            $request->request = new ParameterBag((array)$data);
        }
    }
}
