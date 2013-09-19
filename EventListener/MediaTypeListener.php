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

use FOS\RestBundle\Util\MediaTypeMapperInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * This listener handles Request body decoding.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class MediaTypeListener
{
    /**
     * @var MediaTypeMapperInterface
     */
    private $versionMapper;

    /**
     * Constructor.
     *
     * @param MediaTypeMapperInterface $versionMapper
     */
    public function __construct(MediaTypeMapperInterface $versionMapper)
    {
        $this->versionMapper = $versionMapper;
    }

    /**
     * Core request handler
     *
     * @param GetResponseEvent $event The event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $controllerMap = $request->attributes->get('_controller_map');
        if ($controllerMap) {
            $format = $request->getRequestFormat();
            $acceptHeader = $request->getAcceptableContentTypes();
            foreach ($acceptHeader as $mediaType) {
                if ($format === $request->getFormat($mediaType)) {
                    $controller = $this->versionMapper->map($controllerMap, $mediaType);
                    break;
                }
            }

            if (empty($controller)) {
                $controller = $this->versionMapper->fallback($controllerMap);
            }

            if (isset($controller)) {
                $request->attributes->set('_controller', $controller);
            }
        }
    }
}
