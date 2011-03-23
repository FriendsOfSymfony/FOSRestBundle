<?php

namespace FOS\RestBundle\View;

use Sensio\Bundle\FrameworkExtraBundle\View\AnnotationTemplateListener as BaseAnnotationTemplateListener;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpFoundation\Request;

/**
 * The AnnotationTemplateListener class handles the @extra:Template annotation.
 *
 * @author     Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class AnnotationTemplateListener extends BaseAnnotationTemplateListener
{
    /**
     * Renders the template and initializes a new response object with the 
     * rendered template content.
     *
     * @param GetResponseForControllerResultEvent $event A GetResponseForControllerResultEvent instance
     */
    public function onCoreView(GetResponseForControllerResultEvent $event)
    {
        $view = $event->getControllerResult();
        if (!($view instanceOf View)) {
            return parent::onCoreView($event);
        }

        $parameters = $view->getParameters();
        if (!$parameters) {
            $request = $event->getRequest();

            $vars = $request->attributes->get('_template_vars');
            if (!$vars) {
                $vars = $request->attributes->get('_template_default_vars');
            }

            $parameters = array();
            foreach ($vars as $var) {
                $parameters[$var] = $request->attributes->get($var);
            }

            $view->setParameters($parameters);
        }

        $template = $request->attributes->get('_template');
        if ($template) {
            unset($template['engine'], $template['format']);
            $view->setTemplate($template);
        }

        $event->setResponse($view->handle());
    }
}
