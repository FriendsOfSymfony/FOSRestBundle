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

use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent,
    Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;

use Sensio\Bundle\FrameworkExtraBundle\EventListener\TemplateListener;

use FOS\RestBundle\View\View;

/**
 * The ViewResponseListener class handles the View core event as well as the @extra:Template annotation.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class ViewResponseListener extends TemplateListener
{
    /**
     * Renders the parameters and template and initializes a new response object with the
     * rendered content.
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

        $vars = $request->attributes->get('_template_vars');
        if (!$vars) {
            $vars = $request->attributes->get('_template_default_vars');
        }

        if (!empty($vars)) {
            $parameters = (array)$view->getParameters();
            foreach ($vars as $var) {
                if (!array_key_exists($var, $parameters)) {
                    $parameters[$var] = $request->attributes->get($var);
                }
            }
            $view->setParameters($parameters);
        }

        $template = $request->attributes->get('_template');
        if ($template) {
            if ($template instanceof TemplateReference) {
                $template->set('format', null);
                $template->set('engine', null);
            }
            $view->setTemplate($template);
        }

        $event->setResponse($view->handle());
    }
}
