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

use FOS\RestBundle\Exception\NormalizedException;
use FOS\RestBundle\Normalizer\ExceptionNormalizerInterface;
use FOS\RestBundle\Util\ExceptionWrapper;
use FOS\RestBundle\Util\StopFormatListenerException;
use FOS\RestBundle\View\ExceptionWrapperHandlerInterface;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandler;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Debug\Exception\FlattenException;

/**
 * Custom ExceptionController that uses the view layer and supports HTTP response status code mapping.
 */
class ExceptionController implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    private $viewHandler;
    private $debug;
    private $normalizer;

    /**
     * Constructor.
     *
     * @param ViewHandlerInterface              $viewHandler
     * @param bool                              $debug
     * @param ExceptionNormalizerInterface|null $normalizer
     */
    public function __construct(ViewHandlerInterface $viewHandler, $debug, ExceptionNormalizerInterface $normalizer = null)
    {
        $this->viewHandler = $viewHander;
        $this->debug = $debug;
        $this->normalizer = $normalizer;
    }

    /**
     * Creates a new ExceptionWrapper instance that can be overwritten by a custom
     * ExceptionController class.
     *
     * @param array $parameters Template parameters
     *
     * @return ExceptionWrapper ExceptionWrapper instance
     */
    protected function createExceptionWrapper(array $parameters)
    {
        /** @var ExceptionWrapperHandlerInterface $exceptionWrapperHandler */
        $exceptionWrapperHandler = $this->container->get('fos_rest.exception_handler');

        return $exceptionWrapperHandler->wrap($parameters);
    }

    /**
     * Converts an Exception to a Response.
     *
     * @param Request $request
     * @param object  $exception
     *
     * @return Response
     */
    public function showAction(Request $request, $exception)
    {
        try {
            $format = $this->getFormat($request, $request->getRequestFormat());
        } catch (\Exception $e) {
            $format = null;
        }

        if ($this->normalizer->supportsNormalization($exception)) {
            $normalizedException = $this->normalizer->normalize($exception);
        } else {
            $normalizedException = new NormalizedException([
                'message' => 'Unknown error.',
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ]);
        }

        if (null === $format) {
            $message = 'No matching accepted Response format could be determined, while handling: ';
            $message .= $this->getExceptionMessage($exception);

            return $this->createPlainResponse($message, Response::HTTP_NOT_ACCEPTABLE, $exception->getHeaders());
        }

        $currentContent = $this->getAndCleanOutputBuffering(
            $request->headers->get('X-Php-Ob-Level', -1)
        );
        $code = $this->getStatusCode($exception);

        if ($this->viewHandler->isFormatTemplating($format)) {
            $parameters = [
                'info' => $normalizedException,
                'exception' => $exception,
            ];
        } else {
            $parameters = $normalizedException->getNormalizedData();
        }

        $view = View::create($parameters, $normalizedException->getStatusCode(), $normalizedException->getHeaders());
        $view->setFormat($format);

        if ($this->viewHandler->isFormatTemplating($format)) {
            $view->setTemplate($this->findTemplate(
                $request,
                $format,
                $code,
                $request->attributes->get('showException', $this->debug
            )));
        }

        return $this->viewHandler->handle($view);
    }

    /**
     * Returns a Response Object with content type text/plain.
     *
     * @param string $content
     * @param int    $status
     * @param array  $headers
     *
     * @return Response
     */
    private function createPlainResponse($content, $status, $headers)
    {
        $headers['content-type'] = 'text/plain';

        return new Response($content, $status, $headers);
    }

    /**
     * Gets and cleans any content that was already outputted.
     *
     * This code comes from Symfony and should be synchronized on a regular basis
     * see src/Symfony/Bundle/TwigBundle/Controller/ExceptionController.php
     *
     * @return string
     */
    private function getAndCleanOutputBuffering($startObLevel)
    {
        if (ob_get_level() <= $startObLevel) {
            return '';
        }
        Response::closeOutputBuffers($startObLevel + 1, true);

        return ob_get_clean();
    }

    /**
     * Extracts the exception message.
     *
     * @param FlattenException $exception
     * @param array            $exceptionMap
     *
     * @return int|bool
     */
    private function isSubclassOf($exception, $exceptionMap)
    {
        try {
            foreach ($exceptionMap as $exceptionMapClass => $value) {
                if ($value && $exception instanceof $exceptionMapClass) {
                    return $value;
                }
            }
        } catch (\ReflectionException $re) {
            return 'Invalid class in fos_rest.exception.messages: '.$re->getMessage();
        }

        return false;
    }

    /**
     * Determines the format to use for the response.
     *
     * @param Request $request
     * @param string  $format
     *
     * @return string
     */
    protected function getFormat(Request $request, $format)
    {
        try {
            $formatNegotiator = $this->container->get('fos_rest.exception.format_negotiator');
            $accept = $formatNegotiator->getBest('', []);
            if ($accept) {
                $format = $request->getFormat($accept->getType());
            }
            $request->attributes->set('_format', $format);
        } catch (StopFormatListenerException $e) {
            $format = $request->getRequestFormat();
        }

        return $format;
    }

    /**
     * Finds the template for the given format and status code.
     *
     * Note this method needs to be overridden in case another
     * engine than Twig should be supported;
     *
     * This code is inspired by TwigBundle and should be synchronized on a regular basis
     * see src/Symfony/Bundle/TwigBundle/Controller/ExceptionController.php
     *
     * @param Request $request
     * @param string  $format
     * @param int     $statusCode
     * @param bool    $showException
     *
     * @return TemplateReference
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
            if ($this->container->get('templating')->exists($template)) {
                return $template;
            }
        }

        // try to find a template for the given format
        $template = new TemplateReference('TwigBundle', 'Exception', $name, $format, 'twig');
        if ($this->container->get('templating')->exists($template)) {
            return $template;
        }

        // default to a generic HTML exception
        $request->setRequestFormat('html');

        return new TemplateReference('TwigBundle', 'Exception', $showException ? 'exception_full' : $name, 'html', 'twig');
    }
}
