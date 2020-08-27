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

use FOS\RestBundle\Controller\Annotations\View as ViewAnnotation;
use FOS\RestBundle\FOSRestBundle;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Templating\TemplateReferenceInterface;

/**
 * The ViewResponseListener class handles the View core event as well as the "@extra:Template" annotation.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 *
 * @internal
 */
class ViewResponseListener implements EventSubscriberInterface
{
    private $viewHandler;
    private $forceView;

    public function __construct(ViewHandlerInterface $viewHandler, bool $forceView)
    {
        $this->viewHandler = $viewHandler;
        $this->forceView = $forceView;
    }

    /**
     * @param ViewEvent $event
     */
    public function onKernelView($event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->get(FOSRestBundle::ZONE_ATTRIBUTE, true)) {
            return false;
        }

        $configuration = $request->attributes->get('_template');

        $view = $event->getControllerResult();
        if (!$view instanceof View) {
            if (!$configuration instanceof ViewAnnotation && !$this->forceView) {
                return;
            }

            $view = new View($view);
        }

        if ($configuration instanceof ViewAnnotation) {
            if ($configuration->getTemplateVar(false)) {
                $view->setTemplateVar($configuration->getTemplateVar(false), false);
            }
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
            if ($configuration->getSerializerEnableMaxDepthChecks()) {
                $context->setMaxDepth(0, false);
            }
            if (true === $configuration->getSerializerEnableMaxDepthChecks()) {
                $context->enableMaxDepth();
            } elseif (false === $configuration->getSerializerEnableMaxDepthChecks()) {
                $context->disableMaxDepth();
            }

            $owner = $configuration->getOwner();

            if ([] === $owner || null === $owner) {
                $controller = $action = null;
            } else {
                [$controller, $action] = $owner;
            }

            $vars = $this->getDefaultVars($configuration, $controller, $action);
        } else {
            $vars = [];
        }

        if (null === $view->getFormat()) {
            $view->setFormat($request->getRequestFormat());
        }

        if ($this->viewHandler->isFormatTemplating($view->getFormat(), false)
            && !$view->getRoute()
            && !$view->getLocation()
        ) {
            if (0 !== count($vars)) {
                $parameters = (array) $this->viewHandler->prepareTemplateParameters($view, false);
                foreach ($vars as $var) {
                    if (!array_key_exists($var, $parameters)) {
                        $parameters[$var] = $request->attributes->get($var);
                    }
                }
                $view->setData($parameters);
            }

            if ($configuration && ($template = $configuration->getTemplate()) && !$view->getTemplate(false)) {
                if ($template instanceof TemplateReferenceInterface) {
                    $template->set('format', null);
                }

                $view->setTemplate($template, false);
            }
        }

        $response = $this->viewHandler->handle($view, $request);

        $event->setResponse($response);
    }

    public static function getSubscribedEvents(): array
    {
        // Must be executed before SensioFrameworkExtraBundle's listener
        return [
            KernelEvents::VIEW => ['onKernelView', 30],
        ];
    }

    /**
     * @param object $controller
     */
    private function getDefaultVars(Template $template = null, $controller, string $action): array
    {
        if (0 !== count($arguments = $template->getVars())) {
            return $arguments;
        }

        if (!$template instanceof ViewAnnotation || $template->isPopulateDefaultVars(false)) {
            $r = new \ReflectionObject($controller);

            $arguments = [];
            foreach ($r->getMethod($action)->getParameters() as $param) {
                $arguments[] = $param->getName();
            }

            return $arguments;
        }

        return [];
    }
}
