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

use FOS\RestBundle\Decoder\DecoderProviderInterface;
use FOS\RestBundle\Normalizer\ArrayNormalizerInterface;
use FOS\RestBundle\Normalizer\Exception\NormalizationException;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

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
     * @var boolean
     */
    private $throwExceptionOnUnsupportedContentType;

    /**
     * @var ArrayNormalizerInterface
     */
    private $arrayNormalizer;

    /**
     * Constructor.
     *
     * @param DecoderProviderInterface $decoderProvider Provider for fetching decoders
     * @param boolean $throwExceptionOnUnsupportedContentType
     */
    public function __construct(DecoderProviderInterface $decoderProvider, $throwExceptionOnUnsupportedContentType = false)
    {
        $this->decoderProvider = $decoderProvider;
        $this->throwExceptionOnUnsupportedContentType = $throwExceptionOnUnsupportedContentType;
    }

    /**
     * Sets the array normalizer.
     *
     * @param ArrayNormalizerInterface $arrayNormalizer
     */
    public function setArrayNormalizer(ArrayNormalizerInterface $arrayNormalizer)
    {
        $this->arrayNormalizer = $arrayNormalizer;
    }

    /**
     * Core request handler
     *
     * @param GetResponseEvent $event The event
     * @throws BadRequestHttpException
     * @throws UnsupportedMediaTypeHttpException
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!count($request->request->all())
            && in_array($request->getMethod(), array('POST', 'PUT', 'PATCH', 'DELETE'))
        ) {
            $content = $request->getContent();

            if (!empty($content)) {
                $format = $this->getFormatFromRequest($request);

                if (!$this->decoderProvider->supports($format) && !empty($content)) {
                    if ($this->throwExceptionOnUnsupportedContentType) {
                        throw new UnsupportedMediaTypeHttpException("Request body format '$format' not supported");
                    }

                    return;
                }

                $decoder = $this->decoderProvider->getDecoder($format);
                $data = $decoder->decode($content, $format);
                if (is_array($data)) {
                    if (null !== $this->arrayNormalizer) {
                        try {
                            $data = $this->arrayNormalizer->normalize($data);
                        } catch (NormalizationException $e) {
                            throw new BadRequestHttpException($e->getMessage());
                        }
                    }
                    $request->request = new ParameterBag($data);

                    // Reset the method in the current request to support method-overriding
                    $request->setMethod($request->getRealMethod());
                } else {
                    throw new BadRequestHttpException('Invalid ' . $format . ' message received');
                }
            }
        }
    }

    private function getFormatFromRequest($request)
    {
        $contentType = $request->headers->get('Content-Type');

        $format = (null === $contentType) ? $request->getRequestFormat() : $request->getFormat($contentType);

        return $format;
    }
}
