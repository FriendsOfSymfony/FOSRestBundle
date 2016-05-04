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
use FOS\RestBundle\FOSRestBundle;
use FOS\RestBundle\Normalizer\ArrayNormalizerInterface;
use FOS\RestBundle\Normalizer\Exception\NormalizationException;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

/**
 * This listener handles Request body decoding.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 *
 * @internal
 */
class BodyListener
{
    private $decoderProvider;
    private $throwExceptionOnUnsupportedContentType;
    private $defaultFormat;
    private $arrayNormalizer;
    private $normalizeForms;

    /**
     * Constructor.
     *
     * @param DecoderProviderInterface $decoderProvider
     * @param bool                     $throwExceptionOnUnsupportedContentType
     * @param ArrayNormalizerInterface $arrayNormalizer
     * @param bool                     $normalizeForms
     */
    public function __construct(
        DecoderProviderInterface $decoderProvider,
        $throwExceptionOnUnsupportedContentType = false,
        ArrayNormalizerInterface $arrayNormalizer = null,
        $normalizeForms = false
    ) {
        $this->decoderProvider = $decoderProvider;
        $this->throwExceptionOnUnsupportedContentType = $throwExceptionOnUnsupportedContentType;
        $this->arrayNormalizer = $arrayNormalizer;
        $this->normalizeForms = $normalizeForms;
    }

    /**
     * Sets the array normalizer.
     *
     * @param ArrayNormalizerInterface $arrayNormalizer
     *
     * @deprecated To be removed in FOSRestBundle 2.0.0 (constructor injection is used instead).
     */
    public function setArrayNormalizer(ArrayNormalizerInterface $arrayNormalizer)
    {
        $this->arrayNormalizer = $arrayNormalizer;
    }

    /**
     * Sets the fallback format if there's no Content-Type in the request.
     *
     * @param string $defaultFormat
     */
    public function setDefaultFormat($defaultFormat)
    {
        $this->defaultFormat = $defaultFormat;
    }

    /**
     * Core request handler.
     *
     * @param GetResponseEvent $event
     *
     * @throws BadRequestHttpException
     * @throws UnsupportedMediaTypeHttpException
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->get(FOSRestBundle::ZONE_ATTRIBUTE, true)) {
            return;
        }

        $method = $request->getMethod();
        $contentType = $request->headers->get('Content-Type');
        $normalizeRequest = $this->normalizeForms && $this->isFormRequest($request);

        if ($this->isDecodeable($request)) {
            $format = null === $contentType
                ? $request->getRequestFormat()
                : $request->getFormat($contentType);

            $format = $format ?: $this->defaultFormat;

            $content = $request->getContent();

            if (!$this->decoderProvider->supports($format)) {
                if ($this->throwExceptionOnUnsupportedContentType
                    && $this->isNotAnEmptyDeleteRequestWithNoSetContentType($method, $content, $contentType)
                ) {
                    throw new UnsupportedMediaTypeHttpException("Request body format '$format' not supported");
                }

                return;
            }

            if (!empty($content)) {
                $decoder = $this->decoderProvider->getDecoder($format);
                $data = $decoder->decode($content);
                if (is_array($data)) {
                    $request->request = new ParameterBag($data);
                    $normalizeRequest = true;
                } else {
                    throw new BadRequestHttpException('Invalid '.$format.' message received');
                }
            }
        }

        if (null !== $this->arrayNormalizer && $normalizeRequest) {
            $data = $request->request->all();

            try {
                $data = $this->arrayNormalizer->normalize($data);
            } catch (NormalizationException $e) {
                throw new BadRequestHttpException($e->getMessage());
            }

            $request->request = new ParameterBag($data);
        }
    }

    /**
     * Check if the Request is a not a DELETE with no content and no Content-Type.
     *
     * @param $method
     * @param $content
     * @param $contentType
     *
     * @return bool
     */
    private function isNotAnEmptyDeleteRequestWithNoSetContentType($method, $content, $contentType)
    {
        return false === ('DELETE' === $method && empty($content) && empty($contentType));
    }

    /**
     * Check if we should try to decode the body.
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function isDecodeable(Request $request)
    {
        if (!in_array($request->getMethod(), array('POST', 'PUT', 'PATCH', 'DELETE'))) {
            return false;
        }

        return !$this->isFormRequest($request);
    }

    /**
     * Check if the content type indicates a form submission.
     *
     * @param Request $request
     *
     * @return bool
     *
     * @internal
     */
    protected function isFormRequest(Request $request)
    {
        $contentTypeParts = explode(';', $request->headers->get('Content-Type'));

        if (isset($contentTypeParts[0])) {
            return in_array($contentTypeParts[0], array('multipart/form-data', 'application/x-www-form-urlencoded'));
        }

        return false;
    }
}
