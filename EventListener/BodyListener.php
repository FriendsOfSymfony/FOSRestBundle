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
use Symfony\Component\Routing\Router;

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
     * @var array
     */
    private $disabledRoutes;

    /**
     * Constructor.
     *
     * @param DecoderProviderInterface $decoderProvider Provider for fetching decoders
     * @param boolean $throwExceptionOnUnsupportedContentType
     * @param array $disabledRoutes
     */
    public function __construct(DecoderProviderInterface $decoderProvider, $throwExceptionOnUnsupportedContentType = false, $disabledRoutes = array())
    {
        $this->decoderProvider = $decoderProvider;
        $this->throwExceptionOnUnsupportedContentType = $throwExceptionOnUnsupportedContentType;
        $this->disabledRoutes = $disabledRoutes;
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
        $method = $request->getMethod();
        $route = $request->attributes->get('_route');

        if($this->isDisabledRoute($route)) {
            return;
        }

        if (!count($request->request->all())
            && in_array($method, array('POST', 'PUT', 'PATCH', 'DELETE'))
        ) {
            $contentType = $request->headers->get('Content-Type');

            $format = null === $contentType
                ? $request->getRequestFormat()
                : $request->getFormat($contentType);

            $content = $request->getContent();

            if (!$this->decoderProvider->supports($format)) {
                if (
                    $this->throwExceptionOnUnsupportedContentType &&
                    $this->isNotAnEmptyDeleteRequestWithNoSetContentType($method, $content, $contentType)
                ) {
                    throw new UnsupportedMediaTypeHttpException("Request body format '$format' not supported");
                }

                return;
            }

            if (!empty($content)) {
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
                } else {
                    throw new BadRequestHttpException('Invalid ' . $format . ' message received');
                }
            }
        }
    }

    private function isNotAnEmptyDeleteRequestWithNoSetContentType($method, $content, $contentType)
    {
        return false === ('DELETE' === $method && empty($content) && null === $contentType);
    }

    /**
     * Checks if the given route name is excluded in the configuration.
     *
     * @param $route
     * @return bool
     */
    private function isDisabledRoute($route)
    {
        return in_array($route, $this->disabledRoutes);
    }
}
