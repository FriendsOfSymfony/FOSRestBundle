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

use Symfony\Component\HttpKernel\Event\GetResponseEvent,
    Symfony\Component\HttpKernel\Exception\HttpException,
    Symfony\Component\HttpKernel\HttpKernelInterface,
    Symfony\Component\HttpKernel\Log\LoggerInterface,
    Symfony\Component\Routing\RouterInterface,
    Symfony\Component\DependencyInjection\ContainerAware;

use FOS\RestBundle\Response\Codes,
    FOS\RestBundle\Util\AcceptHeaderNegotiatorInterface;

/**
 * Initializes request attributes based on a matching route and
 * determines the request format based on the Accept header
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class RouterListener extends ContainerAware
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var AcceptHeaderNegotiatorInterface
     */
    private $acceptHeaderNegotiator;

    /**
     * @var null|LoggerInterface
     */
    private $logger;

    /**
     * Initialize RouterListener.
     *
     * @param   RouterInterface $router     Router to map requests to controllers
     * @param   AcceptHeaderNegotiatorInterface   $acceptHeaderNegotiator  The content negotiator service to use
     * @param   LoggerInterface $logger     Logger instance
     */
    public function __construct(RouterInterface $router, AcceptHeaderNegotiatorInterface $acceptHeaderNegotiator, LoggerInterface $logger = null)
    {
        $this->router = $router;
        $this->acceptHeaderNegotiator = $acceptHeaderNegotiator;
        $this->logger = $logger;
    }

    /**
     * Determines and sets the Request format
     *
     * @param   GetResponseEvent   $event    The event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if ($request->attributes->has('_controller')) {
            // routing is already done
            return;
        }

        // add attributes based on the path info (routing)
        try {
            $parameters = $this->router->match($request->getPathInfo());
            if (null !== $this->logger) {
                $this->logger->info(sprintf('Matched route "%s" (parameters: %s)', $parameters['_route'], $this->parametersToString($parameters)));
            }

            if (HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
                $extension = isset($parameters['_format']) ? $parameters['_format'] : null;
                $formatPriorities = isset($parameters['_format_priorities']) ? $parameters['_format_priorities'] : array();
                $format = $this->acceptHeaderNegotiator->getBestFormat($request, $formatPriorities, $extension);

                // TODO determine the right controller based on $format

                if (null === $format) {
                    throw new HttpException(Codes::HTTP_NOT_ACCEPTABLE, "No matching accepted Response format could be determined");
                }

                if (false !== strpos($format, '/')) {
                    $format = $request->getFormat($format);
                }

                $request->setRequestFormat($format);
            }

            $request->attributes->add($parameters);
        } catch (ResourceNotFoundException $e) {
            $message = sprintf('No route found for "%s %s"', $request->getMethod(), $request->getPathInfo());

            throw new NotFoundHttpException($message, $e);
        } catch (MethodNotAllowedException $e) {
            $message = sprintf('No route found for "%s %s": Method Not Allowed (Allow: %s)', $request->getMethod(), $request->getPathInfo(), strtoupper(implode(', ', $e->getAllowedMethods())));

            throw new MethodNotAllowedHttpException($e->getAllowedMethods(), $message, $e);
        }

        if (HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            $context = $this->router->getContext();
            $session = $request->getSession();
            if ($locale = $request->attributes->get('_locale')) {
                if ($session) {
                    $session->setLocale($locale);
                }
                $context->setParameter('_locale', $locale);
            } elseif ($session) {
                $context->setParameter('_locale', $session->getLocale());
            }
        }
    }

    private function parametersToString(array $parameters)
    {
        $pieces = array();
        foreach ($parameters as $key => $val) {
            $pieces[] = sprintf('"%s": "%s"', $key, (is_string($val) ? $val : json_encode($val)));
        }

        return implode(', ', $pieces);
    }
}
