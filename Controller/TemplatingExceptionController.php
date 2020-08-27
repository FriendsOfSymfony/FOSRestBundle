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

use FOS\RestBundle\Util\ExceptionValueMap;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Twig\Environment;

/**
 * @deprecated since FOSRestBundle 2.8
 */
abstract class TemplatingExceptionController extends ExceptionController
{
    protected $templating;

    public function __construct(
        ViewHandlerInterface $viewHandler,
        ExceptionValueMap $exceptionCodes,
        $showException,
        $templating
    ) {
        if (!$templating instanceof EngineInterface && !$templating instanceof Environment) {
            throw new \TypeError(sprintf('The fourth argument of %s must be an instance of %s or %s, but %s was given.', __METHOD__, EngineInterface::class, Environment::class, is_object($templating) ? get_class($templating) : gettype($templating)));
        }

        parent::__construct($viewHandler, $exceptionCodes, $showException);

        $this->templating = $templating;
    }

    /**
     * Finds the template for the given format and status code.
     *
     * @param int  $statusCode
     * @param bool $showException
     *
     * @return string|TemplateReferenceInterface
     */
    abstract protected function findTemplate(Request $request, $statusCode, $showException);
}
