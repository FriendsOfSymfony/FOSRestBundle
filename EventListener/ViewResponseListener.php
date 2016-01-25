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

use FOS\RestBundle\FOSRestBundle;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
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

    /**
     * Constructor.
     *
     * @param ViewHandlerInterface $viewHandler
     * @param bool                 $forceView
     */
    public function __construct(ViewHandlerInterface $viewHandler, $forceView)
    {
        $this->viewHandler = $viewHandler;
        $this->forceView = $forceView;
    }

    /**
     * Renders the parameters and template and initializes a new response object with the
     * rendered content.
     *
     * @param GetResponseForControllerResultEvent $event
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->get(FOSRestBundle::ZONE_ATTRIBUTE, true)) {
            return false;
        }

        /** @var \FOS\RestBundle\Controller\Annotations\View $configuration */
        $configuration = $request->attributes->get('_view');

        $view = $event->getControllerResult();
        $customViewDefined = true;
        if (!$view instanceof View) {
            if (!$configuration && !$this->forceView) {
                return;
            }

            $view = new View($view);
            $customViewDefined = false;
        }

        if ($configuration) {
            if ($configuration->getTemplateVar()) {
                $view->setTemplateVar($configuration->getTemplateVar());
            }
            if ($configuration->getStatusCode() && (null === $view->getStatusCode() || Response::HTTP_OK === $view->getStatusCode())) {
                $view->setStatusCode($configuration->getStatusCode());
            }

            $context = $view->getContext();
            if ($configuration->getSerializerGroups() && !$customViewDefined) {
                $context->addGroups($configuration->getSerializerGroups());
            }
            if ($configuration->getSerializerEnableMaxDepthChecks()) {
                $context->setMaxDepth(0);
            }

            $populateDefaultVars = $configuration->isPopulateDefaultVars();
        } else {
            $populateDefaultVars = true;
        }

        if (null === $view->getFormat()) {
            $view->setFormat($request->getRequestFormat());
        }

        $vars = $request->attributes->get('_template_vars');
        if (!$vars && $populateDefaultVars) {
            $vars = $request->attributes->get('_template_default_vars');
        }

        if ($this->viewHandler->isFormatTemplating($view->getFormat())
            && !$view->getRoute()
            && !$view->getLocation()
        ) {
            if (!empty($vars)) {
                $parameters = (array) $this->viewHandler->prepareTemplateParameters($view);
                foreach ($vars as $var) {
                    if (!array_key_exists($var, $parameters)) {
                        $parameters[$var] = $request->attributes->get($var);
                    }
                }
                $view->setData($parameters);
            }

            $template = null !== $configuration && $configuration->getTemplate()
                ? $configuration->getTemplate()
                : $request->attributes->get('_template');
            if ($template && !$view->getTemplate()) {
                if ($template instanceof TemplateReferenceInterface) {
                    $template->set('format', null);
                }

                $view->setTemplate($template);
            }
        }

        $response = $this->viewHandler->handle($view, $request);

        $event->setResponse($response);
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::VIEW => 'onKernelView',
        );
    }
}
