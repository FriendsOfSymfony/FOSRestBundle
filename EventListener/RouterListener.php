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
    Symfony\Component\Serializer\SerializerInterface,
    Symfony\Component\HttpKernel\Exception\HttpException,
    Symfony\Component\HttpKernel\HttpKernelInterface,
    Symfony\Component\HttpKernel\Log\LoggerInterface,
    Symfony\Component\Routing\RouterInterface,
    Symfony\Component\DependencyInjection\ContainerAware;

use Doctrine\Common\Annotations\Reader;

use FOS\RestBundle\Response\Codes,
    FOS\RestBundle\Util\AcceptHeaderNegotiatorInterface;

/**
 * This listener handles Accept header format negotiations.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class RouterListener extends ContainerAware
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var Reader
     */
    private $reader;

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
     * @param   Reader          $reader     annotations reader
     * @param   AcceptHeaderNegotiatorInterface   $acceptHeaderNegotiator  The content negotiator service to use
     * @param   LoggerInterface $logger     Logger instance
     */
    public function __construct(RouterInterface $router, Reader $reader, AcceptHeaderNegotiatorInterface $acceptHeaderNegotiator, LoggerInterface $logger = null)
    {
        $this->router = $router;
        $this->reader = $reader;
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

            if (isset($parameters['_controller'])) {
                $controller = explode(':', $parameters['_controller']);
                $controller = reset($controller);
                if ($this->container->has($controller)) {
                    $class = get_class($this->container->get($controller));
                } else {
                    // TODO handle non service notation
                }

                if (empty($class)) {
                    throw new \InvalidArgumentException(sprintf('Class could not be determined for Controller identified by "%s".', $parameters['_controller']));
                }

                $class = new \ReflectionClass($class);
                // TODO do something with the versions
                $versions = $this->readAnnotation($class, 'Versions', true);
                $formatPriorities = $this->readAnnotation($class, 'FormatPriorities', true);

                $format = null;
                if (!empty($formatPriorities)) {
                    if (isset($parameters['_format'])) {
                        $request->attributes->set('_format', $parameters['_format']);
                    }
                    $format = $this->acceptHeaderNegotiator->getBestFormat($request, $formatPriorities);
                }

                if (null === $format) {
                    if ($event->getRequestType() === HttpKernelInterface::MASTER_REQUEST)  {
                        throw new HttpException(Codes::HTTP_NOT_ACCEPTABLE, "No matching accepted Response format could be determined");
                    }

                    return;
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

    private function readAnnotation($class, $name, $explode = false)
    {
        $AnnotationClass = 'FOS\RestBundle\Controller\Annotations\\'.$name;
        $value = $this->reader->getClassAnnotation($class, $AnnotationClass);
        if ($explode && $value) {
            $value = explode(',', $value->value);
            array_walk($value, function(&$val){$val = trim($val);});
        }

        return $value;
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
