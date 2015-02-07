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
use FOS\RestBundle\Util\ViolationFormatterInterface;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ValidatorInterface as LegacyValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
    private $paramReader;
    private $request;
    private $params;
    private $validator;
    private $violationFormatter;

    /**
     * @var callable
     */
    private $controller;

    /**
     * Initializes fetcher.
     *
     * @param ParamReader                 $paramReader
     * @param Request                     $request
     * @param ValidatorInterface|LegacyValidatorInterface $validator
     * @param ViolationFormatterInterface $violationFormatter
     */
    public function __construct(ParamReader $paramReader, Request $request, ViolationFormatterInterface $violationFormatter, $validator = null)
    {
        $this->paramReader        = $paramReader;
        $this->request            = $request;
        $this->violationFormatter = $violationFormatter;
        $this->validator          = $validator;

        if ($validator !== null && !$validator instanceof LegacyValidatorInterface && !$validator instanceof ValidatorInterface) {
            throw new \InvalidArgumentException(sprintf(
                'Validator has expected to be an instance of %s or %s, "%s" given',
                'Symfony\Component\Validator\ValidatorInterface',
                'Symfony\Component\Validator\Validator\ValidatorInterface',
                get_class($validator)
            ));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }

    /**
     * Add additional params to the ParamFetcher during runtime.
     *
     * Note that adding a param that has the same name as an existing param will override that param.
     *
     * @param Param $param
     */
    public function addParam(Param $param)
    {
        $this->getParams(); // init params
        $this->params[$param->name] = $param;
    }

    /**
     * @return Param[]
     */
    public function getParams()
    {
        if (null === $this->params) {
            $this->initParams();
        }

        return $this->params;
    }

    /**
     * {@inheritDoc}
     */
    public function get($name, $strict = null)
    {
        $params = $this->getParams();

        if (!array_key_exists($name, $params)) {
            throw new \InvalidArgumentException(sprintf("No @QueryParam/@RequestParam configuration for parameter '%s'.", $name));
        }

        /** @var Param $config */
        $config   = $params[$name];
        $nullable = $config->nullable;
        $default  = $config->default;
        $paramType = $config instanceof QueryParam ? 'Query' : 'Request';

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

            if (($default !== null || !$strict) || $nullable) {
                $default = (array) $default;
            }

            if (!is_array($param)) {
                if ($strict && !$nullable) {
                    throw new BadRequestHttpException(
                        sprintf("%s parameter value of '%s' is not an array", $paramType, $name)
                    );
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
     * @param Param  $config
     * @param string $param
     * @param bool   $strict
     *
     * @return string
     *
     * @throws BadRequestHttpException
     * @throws \RuntimeException
     */
    public function cleanParamWithRequirements(Param $config, $param, $strict)
    {
        $default = $config->default;
        $paramType = $config instanceof QueryParam ? 'Query' : 'Request';

        if (null !== $config->requirements && null === $this->validator) {
            throw new \RuntimeException(
                'The ParamFetcher requirements feature requires the symfony/validator component.'
            );
        }

        if (null === $config->requirements || ($param === $default && null !== $default)) {
            return $param;
        }

        $constraint = $config->requirements;

        if (is_scalar($constraint)) {
            if (is_array($param)) {
                if ($strict) {
                    throw new BadRequestHttpException(
                        sprintf("%s parameter is an array", $paramType)
                    );
                }

                return $default;
            }

            $constraint = new Regex(array(
                'pattern' => '#^'.$config->requirements.'$#xsu',
                'message' => sprintf(
                    "%s parameter value '%s', does not match requirements '%s'",
                    $paramType,
                    $param,
                    $config->requirements
                ),
            ));

            if (false === $config->allowBlank) {
                $constraint = array(new NotBlank(), $constraint);
            }
        }

        if ($this->validator instanceof ValidatorInterface) {
            $errors = $this->validator->validate($param, $constraint);
        } else {
            $errors = $this->validator->validateValue($param, $constraint);
        }

        if (0 !== count($errors)) {
            if ($strict) {
                throw new BadRequestHttpException($this->violationFormatter->formatList($config, $errors));
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
        $configuredParams = $this->getParams();

        $params = array();
        foreach ($configuredParams as $name => $config) {
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
            throw new \InvalidArgumentException(
                'Controller needs to be set as a class instance (closures/functions are not supported)'
            );
        }

        $this->params = $this->paramReader->read(
            new \ReflectionClass(ClassUtils::getClass($this->controller[0])),
            $this->controller[1]
        );
    }
}
