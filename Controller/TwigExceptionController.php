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

use Symfony\Component\HttpFoundation\Request;

/**
 * Custom ExceptionController that uses the view layer and supports HTTP response status code mapping.
 * It additionally is able to prepare the template parameters for the core EngineInterface.
 */
class TwigExceptionController extends TemplatingExceptionController
{
    /**
     * {@inheritdoc}
     */
    protected function createView(\Exception $exception, $code, array $templateData, Request $request, $showException)
    {
        $view = parent::createView($exception, $code, $templateData, $request, $showException);
        $view->setTemplate($this->findTemplate($request, $code, $showException));

        return $view;
    }

    /**
     * {@inheritdoc}
     *
     * This code is inspired by TwigBundle and should be synchronized on a regular basis
     * see src/Symfony/Bundle/TwigBundle/Controller/ExceptionController.php
     */
    protected function findTemplate(Request $request, $statusCode, $showException)
    {
        $format = $request->getRequestFormat();

        $name = $showException ? 'exception' : 'error';
        if ($showException && 'html' == $format) {
            $name = 'exception_full';
        }

        // For error pages, try to find a template for the specific HTTP status code and format
        if (!$showException) {
            $template = sprintf('@Twig/Exception/%s%s.%s.twig', $name, $statusCode, $format);
            if ($this->templating->exists($template)) {
                return $template;
            }
        }

        // try to find a template for the given format
        $template = sprintf('@Twig/Exception/%s.%s.twig', $name, $format);
        if ($this->templating->exists($template)) {
            return $template;
        }

        // default to a generic HTML exception
        $request->setRequestFormat('html');

        return sprintf('@Twig/Exception/%s.html.twig', $showException ? 'exception_full' : $name);
    }
}
