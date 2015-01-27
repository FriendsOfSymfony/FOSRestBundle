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

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\FOSRestBundle;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\Util\ContextHelper;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\TemplateListener;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\Templating\TemplateReferenceInterface;

/**
 * The ViewResponseListener class handles the View core event as well as the "@extra:Template" annotation.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 *
 * @internal
 */
class ViewResponseListener extends TemplateListener
{
    /**
     * Guesses the template name to render and its variables and adds them to
     * the request object.
     *
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->get(FOSRestBundle::ZONE_ATTRIBUTE, true)) {
            return;
        }

        if ($configuration = $request->attributes->get('_view')) {
            $request->attributes->set('_template', $configuration);
        }

        parent::onKernelController($event);
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
            if (!$configuration && !$this->container->getParameter('fos_rest.view_response_listener.force_view')) {
                return parent::onKernelView($event);
            }

            $view = new View($view);
            $customViewDefined = false;
        }

        if ($configuration) {
            if ($configuration->getTemplateVar()) {
                $view->setTemplateVar($configuration->getTemplateVar());
            }
            if ($configuration->getStatusCode() && (null === $view->getStatusCode() || Codes::HTTP_OK === $view->getStatusCode())) {
                $view->setStatusCode($configuration->getStatusCode());
            }

            // BC < 1.8
            $viewClass = 'FOS\RestBundle\View\View';
            if (get_class($view) === $viewClass) {
                $context = $view->getContext();
            } else {
                $method = new \ReflectionMethod($view, 'getSerializationContext');
                if ($method->getDeclaringClass()->getName() != $viewClass) {
                    $context = $view->getSerializationContext();
                } else {
                    $context = $view->getContext();
                }
            }
            $context = $context ?: new Context();

            if ($configuration->getSerializerGroups() && !$customViewDefined) {
                ContextHelper::addGroups($context, $configuration->getSerializerGroups());
            }
            if ($configuration->getSerializerEnableMaxDepthChecks()) {
                ContextHelper::setMaxDepth($context, 0);
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

        /** @var ViewHandlerInterface $viewHandler */
        $viewHandler = $this->container->get('fos_rest.view_handler');

        if ($viewHandler->isFormatTemplating($view->getFormat())
            && !$view->getRoute()
            && !$view->getLocation()
        ) {
            if (!empty($vars)) {
                $parameters = (array) $viewHandler->prepareTemplateParameters($view);

                foreach ($vars as $var) {
                    if (!array_key_exists($var, $parameters)) {
                        $parameters[$var] = $request->attributes->get($var);
                    }
                }
                $view->setData($parameters);
            }

            $template = $request->attributes->get('_template');
            if ($template && !$view->getTemplate()) {
                if ($template instanceof TemplateReferenceInterface) {
                    $template->set('format', null);
                }

                $view->setTemplate($template);
            }
        }

        $response = $viewHandler->handle($view, $request);

        $event->setResponse($response);
    }
}
