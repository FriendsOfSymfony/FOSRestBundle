<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;

/**
 * Custom ExceptionController that uses the view layer and supports HTTP response status code mapping.
 * It additionally is able to prepare the template parameters for the core EngineInterface.
 */
class TwigExceptionController extends TemplatingExceptionController
{
    /**
     * {@inheritdoc}
     */
    protected function createView($format, FlattenException $exception, $code, $parameters, Request $request, $showException)
    {
        $view = parent::createView($format, $exception, $code, $parameters, $request, $showException);

        if ($this->getViewHandler()->isFormatTemplating($format)) {
            $view->setTemplate($this->findTemplate($request, $format, $code, $showException));
            $view->setData($parameters);
        }

        return $view;
    }

    /**
     * {@inheritdoc}
     */
    protected function getParameters($currentContent, $code, $exception, DebugLoggerInterface $logger = null, $format = 'html')
    {
        $parameters = parent::getParameters($currentContent, $code, $exception, $logger, $format);

        if ($this->getViewHandler()->isFormatTemplating($format)) {
            $parameters['logger'] = $logger;
        }

        return $parameters;
    }

    /**
     * {@inheritdoc}
     *
     * This code is inspired by TwigBundle and should be synchronized on a regular basis
     * see src/Symfony/Bundle/TwigBundle/Controller/ExceptionController.php
     */
    protected function findTemplate(Request $request, $format, $statusCode, $showException)
    {
        $name = $showException ? 'exception' : 'error';
        if ($showException && 'html' == $format) {
            $name = 'exception_full';
        }

        // when not in debug, try to find a template for the specific HTTP status code and format
        if (!$showException) {
            $template = new TemplateReference('TwigBundle', 'Exception', $name.$statusCode, $format, 'twig');
            if ($this->templating->exists($template)) {
                return $template;
            }
        }

        // try to find a template for the given format
        $template = new TemplateReference('TwigBundle', 'Exception', $name, $format, 'twig');
        if ($this->templating->exists($template)) {
            return $template;
        }

        // default to a generic HTML exception
        $request->setRequestFormat('html');

        return new TemplateReference('TwigBundle', 'Exception', $showException ? 'exception_full' : $name, 'html', 'twig');
    }
}
