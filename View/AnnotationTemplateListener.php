<?php

namespace FOS\RestBundle\View;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent,
    Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;

use Sensio\Bundle\FrameworkExtraBundle\EventListener\TemplateListener as BaseAnnotationTemplateListener;

/*
 * This file is part of the FOSRestBundle
 *
 * (c) Lukas Kahwe Smith <smith@pooteeweet.org>
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 * (c) Bulat Shakirzyanov <mallluhuct@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

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

        $request = $event->getRequest();
        $parameters = $view->getParameters();
        if (!$parameters) {

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
            $template->set('format', null);
            $template->set('engine', null);
            $view->setTemplate($template);
        }

        $event->setResponse($view->handle());
    }
}
