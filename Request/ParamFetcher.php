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

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\Param;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Helper to validate parameters of the active request.
 *
 * @author Alexander <iam.asm89@gmail.com>
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author Boris Guéry <guery.b@gmail.com>
 */
class ParamFetcher implements ParamFetcherInterface
{
    /**
     * @var ParamReader
     */
    private $paramReader;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var array
     */
    private $params;

    /**
     * @var callable
     */
    private $controller;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * Initializes fetcher.
     *
     * @param ParamReader        $paramReader Query param reader
     * @param Request            $request     Active request
     * @param ValidatorInterface $validator   The validator service
     */
    public function __construct(ParamReader $paramReader, Request $request, ValidatorInterface $validator)
    {
        $this->paramReader = $paramReader;
        $this->request     = $request;
        $this->validator   = $validator;
    }

    /**
     * {@inheritDoc}
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }

    /**
     * {@inheritDoc}
     */
    public function get($name, $strict = null)
    {
        if (null === $this->params) {
            $this->initParams();
        }

        if (!array_key_exists($name, $this->params)) {
            throw new \InvalidArgumentException(sprintf("No @QueryParam/@RequestParam configuration for parameter '%s'.", $name));
        }

        /** @var Param $config */
        $config   = $this->params[$name];
        $nullable = $config->nullable;
        $default  = $config->default;

        if ($config->array) {
            $default = (array) $default;
        }

        if (null === $strict) {
            $strict = $config->strict;
        }

        if ($config instanceof RequestParam) {
            $param = $this->request->request->get($config->getKey(), $default);
        } elseif ($config instanceof QueryParam) {
            $param = $this->request->query->get($config->getKey(), $default);
        } else {
            $param = null;
        }

        if ($config->array) {
            if (!is_array($param)) {
                if ($strict) {
                    throw new BadRequestHttpException(sprintf("Query parameter value of '%s' is not an array", $name));
                }

                return $default;
            }

            $self = $this;
            array_walk($param, function (&$data) use ($config, $strict, $self) {
                $data = $self->cleanParamWithRequirements($config, $data, $strict);
            });

            return $param;
        }

        if (!is_scalar($param)) {
            if (!$nullable) {
                if ($strict) {
                    $paramType = $config instanceof QueryParam ? 'Query' : 'Request';
                    $problem = empty($param) ? 'empty' : 'not a scalar';

                    throw new BadRequestHttpException(
                        sprintf('%s parameter "%s" is %s', $paramType, $name, $problem)
                    );
                }

                return $this->cleanParamWithRequirements($config, $param, $strict);
            }

            return $default;
        }

        return $this->cleanParamWithRequirements($config, $param, $strict);
    }

    /**
     * @param Param   $config config
     * @param string  $param  param to clean
     * @param boolean $strict is strict
     *
     * @throws BadRequestHttpException
     * @return string
     */
    public function cleanParamWithRequirements(Param $config, $param, $strict)
    {
        $default = $config->default;

        if (null === $config->requirements || ($param === $default && null !== $default)) {

            return $param;
        }

        $constraint = $config->requirements;

        if (is_scalar($constraint)) {
            if (is_array($param)) {
                if ($strict) {
                    throw new BadRequestHttpException("Query parameter is an array");
                }

                return $default;
            }

            $constraint = new Regex(array(
                'pattern' => '#^'.$config->requirements.'$#xsu',
                'message' => sprintf(
                    "%s parameter value '%s', does not match requirements '%s'",
                    $config instanceof QueryParam ? 'Query' : 'Request',
                    $param,
                    $config->requirements
                )
            ));
        }

        $errors = $this->validator->validateValue($param, $constraint);

        if (0 !== count($errors)) {

            if ($strict) {
                throw new BadRequestHttpException((string) $errors);
            }

            return null === $default ? '' : $default;
        }

        return $param;
    }

    /**
     * {@inheritDoc}
     */
    public function all($strict = null)
    {
        if (null === $this->params) {
            $this->initParams();
        }

        $params = array();
        foreach ($this->params as $name => $config) {
            $params[$name] = $this->get($name, $strict);
        }

        return $params;
    }

    /**
     * Initialize the parameters
     *
     * @throws \InvalidArgumentException
     */
    private function initParams()
    {
        if (empty($this->controller)) {
            throw new \InvalidArgumentException('Controller and method needs to be set via setController');
        }

        if (!is_array($this->controller) || empty($this->controller[0]) || !is_object($this->controller[0])) {
            throw new \InvalidArgumentException('Controller needs to be set as a class instance (closures/functions are not supported)');
        }

        $this->params = $this->paramReader->read(new \ReflectionClass(ClassUtils::getClass($this->controller[0])), $this->controller[1]);
    }
}
