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
use Symfony\Component\Templating\EngineInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Loader\ExistsLoaderInterface;

/**
 * Custom ExceptionController that uses the view layer and supports HTTP response status code mapping.
 * It additionally is able to prepare the template parameters for the core EngineInterface.
 *
 * @deprecated since FOSRestBundle 2.8
 */
class TwigExceptionController extends TemplatingExceptionController
{
    /**
     * {@inheritdoc}
     */
    protected function createView(\Throwable $exception, $code, array $templateData, Request $request, $showException)
    {
        $view = parent::createView($exception, $code, $templateData, $request, $showException);
        $view->setTemplate($this->findTemplate($request, $code, $showException), false);

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
            if (
                ($this->templating instanceof EngineInterface && $this->templating->exists($template)) ||
                ($this->templating instanceof Environment && $this->templateExists($template))
            ) {
                return $template;
            }
        }

        // try to find a template for the given format
        $template = sprintf('@Twig/Exception/%s.%s.twig', $name, $format);
        if (
            ($this->templating instanceof EngineInterface && $this->templating->exists($template)) ||
            ($this->templating instanceof Environment && $this->templateExists($template))
        ) {
            return $template;
        }

        // default to a generic HTML exception
        $request->setRequestFormat('html');

        return sprintf('@Twig/Exception/%s.html.twig', $showException ? 'exception_full' : $name);
    }

    /**
     * See if a template exists using the modern Twig mechanism.
     *
     * This code is based on TwigBundle and should be removed when the minimum required
     * version of Twig is >= 3.0. See src/Symfony/Bundle/TwigBundle/Controller/ExceptionController.php
     */
    private function templateExists(string $template): bool
    {
        $loader = $this->templating->getLoader();
        if ($loader instanceof ExistsLoaderInterface || method_exists($loader, 'exists')) {
            return $loader->exists($template);
        }

        try {
            $loader->getSourceContext($template)->getCode();

            return true;
        } catch (LoaderError $e) {
        }

        return false;
    }
}
