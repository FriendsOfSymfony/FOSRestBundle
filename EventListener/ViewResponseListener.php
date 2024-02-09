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

use Doctrine\Common\Annotations\Reader;
use Doctrine\Persistence\Proxy;
use FOS\RestBundle\Controller\Annotations\View as ViewAnnotation;
use FOS\RestBundle\FOSRestBundle;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * The ViewResponseListener class handles the kernel.view event and creates a {@see Response} for a {@see View} provided by the controller result.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 *
 * @internal
 */
class ViewResponseListener implements EventSubscriberInterface
{
    /**
     * @var ViewHandlerInterface
     */
    private $viewHandler;

    private $forceView;

    /**
     * @var Reader|null
     */
    private $annotationReader;

    public function __construct(ViewHandlerInterface $viewHandler, bool $forceView, ?Reader $annotationReader = null)
    {
        $this->viewHandler = $viewHandler;
        $this->forceView = $forceView;
        $this->annotationReader = $annotationReader;
    }

    /**
     * Extracts configuration for a {@see ViewAnnotation} from the controller if present.
     */
    public function onKernelController(ControllerEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->get(FOSRestBundle::ZONE_ATTRIBUTE, true)) {
            return;
        }

        $controller = $event->getController();

        if (!\is_array($controller) && method_exists($controller, '__invoke')) {
            $controller = [$controller, '__invoke'];
        }

        if (!\is_array($controller)) {
            return;
        }

        $className = $this->getRealClass(\get_class($controller[0]));
        $object = new \ReflectionClass($className);
        $method = $object->getMethod($controller[1]);

        /** @var ViewAnnotation|null $classConfiguration */
        $classConfiguration = null;

        /** @var ViewAnnotation|null $methodConfiguration */
        $methodConfiguration = null;

        if (null !== $this->annotationReader) {
            $classConfiguration = $this->getViewConfiguration($this->annotationReader->getClassAnnotations($object));
            $methodConfiguration = $this->getViewConfiguration($this->annotationReader->getMethodAnnotations($method));
        }

        if (80000 <= \PHP_VERSION_ID) {
            if (null === $classConfiguration) {
                $classAttributes = array_map(
                    function (\ReflectionAttribute $attribute) {
                        return $attribute->newInstance();
                    },
                    $object->getAttributes(ViewAnnotation::class, \ReflectionAttribute::IS_INSTANCEOF)
                );

                $classConfiguration = $this->getViewConfiguration($classAttributes);
            }

            if (null === $methodConfiguration) {
                $methodAttributes = array_map(
                    function (\ReflectionAttribute $attribute) {
                        return $attribute->newInstance();
                    },
                    $method->getAttributes(ViewAnnotation::class, \ReflectionAttribute::IS_INSTANCEOF)
                );

                $methodConfiguration = $this->getViewConfiguration($methodAttributes);
            }
        }

        // An annotation/attribute on the method takes precedence over the class level
        if (null !== $methodConfiguration) {
            $request->attributes->set(FOSRestBundle::VIEW_ATTRIBUTE, $methodConfiguration);
        } elseif (null !== $classConfiguration) {
            $request->attributes->set(FOSRestBundle::VIEW_ATTRIBUTE, $classConfiguration);
        }
    }

    public function onKernelView(ViewEvent $event): void
    {
        $request = $event->getRequest();

        if (!$request->attributes->get(FOSRestBundle::ZONE_ATTRIBUTE, true)) {
            return;
        }

        /** @var ViewAnnotation|null $configuration */
        $configuration = $request->attributes->get(FOSRestBundle::VIEW_ATTRIBUTE);

        $view = $event->getControllerResult();
        if (!$view instanceof View) {
            if (!$configuration instanceof ViewAnnotation && !$this->forceView) {
                return;
            }

            $view = new View($view);
        }

        if ($configuration instanceof ViewAnnotation) {
            if (null !== $configuration->getStatusCode() && (null === $view->getStatusCode() || Response::HTTP_OK === $view->getStatusCode())) {
                $view->setStatusCode($configuration->getStatusCode());
            }

            $context = $view->getContext();
            if ($configuration->getSerializerGroups()) {
                if (null === $context->getGroups()) {
                    $context->setGroups($configuration->getSerializerGroups());
                } else {
                    $context->setGroups(array_merge($context->getGroups(), $configuration->getSerializerGroups()));
                }
            }
            if (true === $configuration->getSerializerEnableMaxDepthChecks()) {
                $context->enableMaxDepth();
            } elseif (false === $configuration->getSerializerEnableMaxDepthChecks()) {
                $context->disableMaxDepth();
            }
        }

        if (null === $view->getFormat()) {
            $view->setFormat($request->getRequestFormat());
        }

        $event->setResponse($this->viewHandler->handle($view, $request));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
            KernelEvents::VIEW => ['onKernelView', -128],
        ];
    }

    /**
     * @param object[] $annotations
     */
    private function getViewConfiguration(array $annotations): ?ViewAnnotation
    {
        $viewAnnotation = null;

        foreach ($annotations as $annotation) {
            if (!$annotation instanceof ViewAnnotation) {
                continue;
            }

            if (null === $viewAnnotation) {
                $viewAnnotation = $annotation;
            } else {
                throw new \LogicException('Multiple "view" annotations are not allowed.');
            }
        }

        return $viewAnnotation;
    }

    private function getRealClass(string $class): string
    {
        if (class_exists(Proxy::class)) {
            if (false === $pos = strrpos($class, '\\'.Proxy::MARKER.'\\')) {
                return $class;
            }

            return substr($class, $pos + Proxy::MARKER_LENGTH + 2);
        }

        return $class;
    }
}
