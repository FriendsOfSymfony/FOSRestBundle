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
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\TemplateReferenceInterface;

abstract class TemplatingExceptionController extends ExceptionController
{
    protected $templating;

    public function __construct(
        ViewHandlerInterface $viewHandler,
        ExceptionValueMap $exceptionCodes,
        $showException,
        EngineInterface $templating
    ) {
        parent::__construct($viewHandler, $exceptionCodes, $showException);

        $this->templating = $templating;
    }

    /**
     * Finds the template for the given format and status code.
     *
     * @param Request $request
     * @param int     $statusCode
     * @param bool    $showException
     *
     * @return TemplateReferenceInterface
     */
    abstract protected function findTemplate(Request $request, $statusCode, $showException);
}
