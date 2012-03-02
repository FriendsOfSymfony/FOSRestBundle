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

use FOS\Rest\Decoder\DecoderProviderInterface;

use Symfony\Component\HttpFoundation\ParameterBag,
    Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * This listener handles Request body decoding.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class BodyListener
{
    /**
     * @var DecoderProviderInterface
     */
    private $decoderProvider;

    /**
     * Constructor.
     *
     * @param   DecoderProviderInterface $decoderProvider Provider for fetching decoders
     */
    public function __construct(DecoderProviderInterface $decoderProvider)
    {
        $this->decoderProvider = $decoderProvider;
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

            if (!$this->decoderProvider->supports($format)) {
                return;
            }

            $decoder = $this->decoderProvider->getDecoder($format);

            $data = $decoder->decode($request->getContent(), $format);
            if (is_array($data)) {
                $request->request = new ParameterBag($data);
            }
        }
    }
}
