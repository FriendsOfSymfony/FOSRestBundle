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

use FOS\RestBundle\View\RedirectView;

use FOS\RestBundle\View\RouteRedirectView;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent,
    Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent,
    Symfony\Bundle\FrameworkBundle\Templating\TemplateReference,
    Symfony\Component\DependencyInjection\ContainerInterface;

use FOS\RestBundle\View\View,
    FOS\RestBundle\Controller\Annotations\View as ViewAnnotation;

/**
 * The ViewResponseListener class handles the View core event as well as the @extra:Template annotation.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class ViewResponseListener
{
    /**
     * @var Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container The service container instance
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Guesses the template name to render and its variables and adds them to
     * the request object.
     *
     * @param FilterControllerEvent $event A FilterControllerEvent instance
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();
        if (!$configuration = $request->attributes->get('_view')) {
            return;
        }

        $request->attributes->set('_template', $configuration);
    }

    /**
     * Renders the parameters and template and initializes a new response object with the
     * rendered content.
     *
     * @param GetResponseForControllerResultEvent $event A GetResponseForControllerResultEvent instance
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $view = $event->getControllerResult();

        $request = $event->getRequest();
        if ($request->attributes->get('_view')) {
            $view = new View($view);
        }

        if (!$view instanceOf View) {
            return;
        }

        if (!$vars = $request->attributes->get('_template_vars')) {
            $vars = $request->attributes->get('_template_default_vars');
        }

        if (!empty($vars)) {
            $parameters = $view->getData();
            if (null !== $parameters && !is_array($parameters)) {
                throw new \RuntimeException('View data must be an array if using a templating aware format.');
            }

            $parameters = (array)$parameters;
            foreach ($vars as $var) {
                if (!array_key_exists($var, $parameters)) {
                    $parameters[$var] = $request->attributes->get($var);
                }
            }
            $view->setData($parameters);
        }

        if ($template = $request->attributes->get('_template')) {
            if ($template instanceof TemplateReference) {
                $template->set('format', null);
                $template->set('engine', null);
            }
            $view->setTemplate($template);
        }

        $handler = $this->container->get('fos_rest.view_handler');
        $event->setResponse($handler->handle($view, $request));
    }
}
