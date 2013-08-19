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

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use FOS\RestBundle\Routing\HateoasCollectionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;

use Sensio\Bundle\FrameworkExtraBundle\EventListener\TemplateListener;

use JMS\Serializer\SerializationContext;

use FOS\RestBundle\View\View;

use FOS\Rest\Util\Codes;

/**
 * The ViewResponseListener class handles the View core event as well as the "@extra:Template" annotation.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class ViewResponseListener extends TemplateListener
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

        if ($configuration = $request->attributes->get('_view')) {
            $request->attributes->set('_template', $configuration);
        }

        parent::onKernelController($event);
    }

    /**
     * Renders the parameters and template and initializes a new response object with the
     * rendered content.
     *
     * @param GetResponseForControllerResultEvent $event A GetResponseForControllerResultEvent instance
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();
        $configuration = $request->attributes->get('_view');

        $view = $event->getControllerResult();
        if (!$view instanceOf View) {
            if (!$configuration && !$this->container->getParameter('fos_rest.view_response_listener.force_view')) {
                return parent::onKernelView($event);
            }

            $view = new View($view);
        }

        if ($configuration) {
            if ($configuration->getTemplateVar()) {
                $view->setTemplateVar($configuration->getTemplateVar());
            }
            if ($configuration->getStatusCode() && (null === $view->getStatusCode() || Codes::HTTP_OK === $view->getStatusCode())) {
                $view->setStatusCode($configuration->getStatusCode());
            }
            if ($configuration->getSerializerGroups()) {
                $context = $view->getSerializationContext() ?: new SerializationContext();
                $context->setGroups($configuration->getSerializerGroups());
                $view->setSerializationContext($context);
            }
        }

        if (null === $view->getFormat()) {
            $view->setFormat($request->getRequestFormat());
        }

        $vars = $request->attributes->get('_template_vars');
        if (!$vars) {
            $vars = $request->attributes->get('_template_default_vars');
        }

        $viewHandler = $this->container->get('fos_rest.view_handler');

        if ($viewHandler->isFormatTemplating($view->getFormat())) {
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
            if ($template) {
                if ($template instanceof TemplateReference) {
                    $template->set('format', null);
                }

                $view->setTemplate($template);
            }
        } elseif ($this->container->has('fsc_hateoas.metadata.factory')) {
            $data = $view->getData();
            if (is_object($data)) {
                $class = $data instanceof HateoasCollectionInterface
                    ? $data->getSubject() : get_class($data);

                $cacheDir = $this->container->getParameter('kernel.cache_dir');
                $file = $cacheDir.'/fos_rest/hateoas/'.str_replace('\\', '', $class);
                if (file_exists($file)) {
                    $collection = file_get_contents($file);
                    $collection = unserialize($collection);

                    $relationsBuilder = $this->container->get('fsc_hateoas.metadata.relation_builder.factory')->create();
                    $subject = strtolower($collection->getSingularName());
                    $baseParameters = array();
                    if ($collection->isFormatInRoute()
                        && null !== $request->attributes->get('_format')
                    ) {
                        $baseParameters['_format'] = $view->getFormat();
                    }
                    foreach ($collection as $routeName => $route) {
                        $relName = $route->getRelName();
                        if (!$relName) {
                            continue;
                        }

                        $relName = $routeName === $request->attributes->get('_route') ? 'self' : $relName;
                        $parameters = array(
                            'route' => $routeName,
                            'parameters' => $baseParameters,
                        );
                        foreach ($route->getPlaceholders() as $placeholder) {
                            if ($placeholder === $subject) {
                                $value = $data instanceof HateoasCollectionInterface
                                    ? '{'.$placeholder.'}' : $data->{$collection->getIdentifier()}()
                                ;
                            } else {
                                $value = $request->attributes->get($placeholder);
                            }
                            $parameters['parameters'][$placeholder] = $value;
                        }
                        $relationsBuilder->add($relName, $parameters);
                    }

                    $this->container->get('fsc_hateoas.metadata.factory')->addObjectRelations($data, $relationsBuilder->build());
                }
            }
        }

        $response = $viewHandler->handle($view, $request);

        $event->setResponse($response);
    }
}
