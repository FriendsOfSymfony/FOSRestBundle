<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Request;

use Doctrine\Common\Util\ClassUtils;
use FOS\RestBundle\Controller\Annotations\Param;
use Symfony\Component\HttpFoundation\Request;

/**
 * Contains the {@link ParamFetcher} params and links them to a request.
 *
 * @internal
 */
final class ParameterBag
{
    private $paramReader;
    private $params;

    public function __construct(ParamReaderInterface $paramReader)
    {
        $this->paramReader = $paramReader;
        $this->params = new \SplObjectStorage();
    }

    public function getParams(Request $request)
    {
        if (!isset($this->params[$request]) || empty($this->params[$request]['controller'])) {
            throw new \InvalidArgumentException('Controller and method needs to be set via setController.');
        }
        if (null === $this->params[$request]['params']) {
            $this->initParams($request);
        }

        return $this->params[$request]['params'];
    }

    public function addParam(Request $request, Param $param)
    {
        $this->getParams($request);

        $data = $this->params[$request];
        $data['params'][$param->name] = $param;

        $this->params[$request] = $data;
    }

    public function setController(Request $request, $controller)
    {
        $this->params[$request] = array(
            'controller' => $controller,
            'params' => null,
        );
    }

    /**
     * Initialize the parameters.
     *
     * @param Request $request
     *
     * @throws \InvalidArgumentException
     */
    private function initParams(Request $request)
    {
        $controller = $this->params[$request]['controller'];
        if (!is_array($controller) || empty($controller[0]) || !is_object($controller[0])) {
            throw new \InvalidArgumentException(
                'Controller needs to be set as a class instance (closures/functions are not supported)'
            );
        }

        $data = $this->params[$request];
        $data['params'] = $this->paramReader->read(
            new \ReflectionClass(ClassUtils::getClass($controller[0])),
            $controller[1]
        );

        $this->params[$request] = $data;
    }
}
