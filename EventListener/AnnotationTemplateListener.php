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

use Symfony\Component\HttpKernel\Event\FilterControllerEvent,
    Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;

use Sensio\Bundle\FrameworkExtraBundle\EventListener\TemplateListener as BaseAnnotationTemplateListener;

use FOS\RestBundle\View\View;

/**
 * The AnnotationTemplateListener class handles the @extra:Template annotation.
 *
 * @author     Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class AnnotationTemplateListener extends BaseAnnotationTemplateListener
{

    /**
     * Guesses the template name to render and its variables and adds them to
     * the request object.
     *
     * @param FilterControllerEvent $event A FilterControllerEvent instance
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        if (!is_array($controller = $event->getController())) {
            return;
        }

        $request = $event->getRequest();

        if (!$configuration = $request->attributes->get('_template')) {
            return;
        }

        if (!$configuration->getTemplate()) {
            $configuration->setTemplate($this->guessTemplateName($controller, $request));
        }

        $request->attributes->set('_template', $configuration->getTemplate());
        $request->attributes->set('_template_vars', $configuration->getVars());

        // all controller method arguments
        if (!$configuration->getVars()) {
            $r = new \ReflectionObject($controller[0]);

            $vars = array();
            foreach ($r->getMethod($controller[1])->getParameters() as $param) {
                $vars[] = $param->getName();
            }

            $request->attributes->set('_template_default_vars', $vars);
        }
    }

    /**
     * Renders the template and initializes a new response object with the
     * rendered template content.
     *
     * @param GetResponseForControllerResultEvent $event A GetResponseForControllerResultEvent instance
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $view = $event->getControllerResult();
        if (!($view instanceOf View)) {
            return parent::onKernelView($event);
        }

        $request = $event->getRequest();
        $parameters = $view->getParameters();
        if (!$parameters) {

            $vars = $request->attributes->get('_template_vars');
            if (!$vars) {
                $vars = $request->attributes->get('_template_default_vars');
            }

            $parameters = array();
            if (!empty($vars)) {
                foreach ($vars as $var) {
                    $parameters[$var] = $request->attributes->get($var);
                }
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
