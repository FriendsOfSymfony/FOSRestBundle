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

        $configuration = $request->attributes->get('_template');

        $view = $event->getControllerResult();
        if (!$view instanceof View) {
            if (!$configuration instanceof ViewAnnotation && !$this->forceView) {
                return;
            }

            $view = new View($view);
        }

        if ($configuration instanceof ViewAnnotation) {
            if ($configuration->getTemplateVar()) {
                $view->setTemplateVar($configuration->getTemplateVar());
            }
            if (null !== $configuration->getStatusCode() && (null === $view->getStatusCode() || Response::HTTP_OK === $view->getStatusCode())) {
                $view->setStatusCode($configuration->getStatusCode());
            }

            $context = $view->getContext();
            if ($configuration->getSerializerGroups()) {
                $context->addGroups($configuration->getSerializerGroups());
            }
            if ($configuration->getSerializerEnableMaxDepthChecks()) {
                $context->setMaxDepth(0);
            }

            list($controller, $action) = $configuration->getOwner();
            $vars = $this->getDefaultVars($configuration, $controller, $action);
        } else {
            $vars = null;
        }

        if (null === $view->getFormat()) {
            $view->setFormat($request->getRequestFormat());
        }

        if ($this->viewHandler->isFormatTemplating($view->getFormat())
            && !$view->getRoute()
            && !$view->getLocation()
        ) {
            if (null !== $vars && 0 !== count($vars)) {
                $parameters = (array) $this->viewHandler->prepareTemplateParameters($view);
                foreach ($vars as $var) {
                    if (!array_key_exists($var, $parameters)) {
                        $parameters[$var] = $request->attributes->get($var);
                    }
                }
                $view->setData($parameters);
            }

            if ($configuration && ($template = $configuration->getTemplate()) && !$view->getTemplate()) {
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
        // Must be executed before SensioFrameworkExtraBundle's listener
        return array(
            KernelEvents::VIEW => array('onKernelView', 30),
        );
    }

    /**
     * @param Request  $request
     * @param Template $template
     * @param object   $controller
     * @param string   $action
     *
     * @return array
     *
     * @see \Sensio\Bundle\FrameworkExtraBundle\EventListener\TemplateListener::resolveDefaultParameters()
     */
    private function getDefaultVars(Template $template = null, $controller, $action)
    {
        if (0 !== count($arguments = $template->getVars())) {
            return $arguments;
        }

        if (!$template instanceof ViewAnnotation || $template->isPopulateDefaultVars()) {
            $r = new \ReflectionObject($controller);

            $arguments = array();
            foreach ($r->getMethod($action)->getParameters() as $param) {
                $arguments[] = $param->getName();
            }

            return $arguments;
        }
    }
}
