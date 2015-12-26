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
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
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
class ParamFetcher implements ParamFetcherInterface, ContainerAwareInterface
{
    private $parameterBag;
    private $requestStack;
    private $validator;
    private $violationFormatter;
    /**
     * @var callable
     */
    private $controller;
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Initializes fetcher.
     *
     * @param ParamReader                                 $paramReader
     * @param Request|RequestStack                        $request
     * @param ValidatorInterface|LegacyValidatorInterface $validator
     * @param ViolationFormatterInterface                 $violationFormatter
     */
    public function __construct(ParamReader $paramReader, $requestStack = null, ViolationFormatterInterface $violationFormatter, $validator = null)
    {
        if (null === $requestStack || $requestStack instanceof Request) {
            @trigger_error('Support of Symfony\Component\HttpFoundation\Request in FOS\RestBundle\Request\ParamFetcher is deprecated since version 1.7 and will be removed in 2.0. Use Symfony\Component\HttpFoundation\RequestStack instead.', E_USER_DEPRECATED);
        } elseif (!($requestStack instanceof RequestStack)) {
            throw new \InvalidArgumentException(sprintf('Argument 3 of %s constructor must be either an instance of Symfony\Component\HttpFoundation\Request or Symfony\Component\HttpFoundation\RequestStack.', get_class($this)));
        }

        $this->requestStack = $requestStack;
        $this->violationFormatter = $violationFormatter;
        $this->validator = $validator;

        $this->parameterBag = new ParameterBag($paramReader);

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
     * Sets the Container associated with this Controller.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function setController($controller)
    {
        $this->parameterBag->setController($this->getRequest(), $controller);
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
        $this->parameterBag->addParam($this->getRequest(), $param);
    }

    /**
     * @return Param[]
     */
    public function getParams()
    {
        return $this->parameterBag->getParams($this->getRequest());
    }

    /**
     * {@inheritdoc}
     */
    public function get($name, $strict = null)
    {
        $params = $this->getParams();

        if (!array_key_exists($name, $params)) {
            throw new \InvalidArgumentException(sprintf("No @QueryParam/@RequestParam configuration for parameter '%s'.", $name));
        }

        /** @var Param $config */
        $config = $params[$name];
        $nullable = $config->nullable;
        $default = $config->default;
        $paramType = $config instanceof QueryParam ? 'Query' : 'Request';

        if (null === $strict) {
            $strict = $config->strict;
        }

        if ($config instanceof RequestParam) {
            $param = $this->getRequest()->request->get($config->getKey(), $default);
        } elseif ($config instanceof QueryParam) {
            $param = $this->getRequest()->query->get($config->getKey(), $default);
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

        $this->checkNotIncompatibleParams($config);

        if (null === $config->requirements || ($param === $default && null !== $default)) {
            return $param;
        }

        $constraint = $config->requirements;

        if (is_scalar($constraint)) {
            if (is_array($param)) {
                if ($strict) {
                    throw new BadRequestHttpException(
                        sprintf('%s parameter is an array', $paramType)
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
        } elseif (is_array($constraint) && isset($constraint['rule']) && $constraint['error_message']) {
            $constraint = new Regex(array(
                'pattern' => '#^'.$config->requirements['rule'].'$#xsu',
                'message' => $config->requirements['error_message'],
            ));
        }

        if (false === $config->allowBlank) {
            $constraint = array(new NotBlank(), $constraint);
        }

        if ($this->validator instanceof ValidatorInterface) {
            $errors = $this->validator->validate($param, $constraint);
        } else {
            $errors = $this->validator->validateValue($param, $constraint);
        }

        if (0 !== count($errors)) {
            if ($strict) {
                if (is_array($config->requirements) && isset($config->requirements['error_message'])) {
                    $errorMessage = $config->requirements['error_message'];
                } else {
                    $errorMessage = $this->violationFormatter->formatList($config, $errors);
                }
                throw new BadRequestHttpException($errorMessage);
            }

            return null === $default ? '' : $default;
        }

        return $param;
    }

    /**
     * {@inheritdoc}
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
     * Check if current param is not in conflict with other parameters
     * according to the "incompatibles" field.
     *
     * @param Param $config the configuration for the param fetcher
     *
     * @throws BadRequestHttpException
     */
    private function checkNotIncompatibleParams(Param $config)
    {
        if (!$config instanceof QueryParam) {
            return;
        };

        foreach ($config->incompatibles as $incompatibleParam) {
            $isIncompatiblePresent = $this->getRequest()->query->get(
                $incompatibleParam,
                null
            ) !== null;

            if ($isIncompatiblePresent) {
                $exceptionMessage = sprintf(
                    "'%s' param is incompatible with %s param",
                    $config->name,
                    $incompatibleParam
                );

                throw new BadRequestHttpException($exceptionMessage);
            }
        }
    }

    /**
     * @throws \RuntimeException
     *
     * @return Request
     */
    private function getRequest()
    {
        if ($this->requestStack instanceof Request) {
            return $this->requestStack;
        } elseif ($this->requestStack instanceof RequestStack) {
            $request = $this->requestStack->getCurrentRequest();
        } else {
            $request = $this->container->get('request');
        }

        if ($request !== null) {
            return $request;
        }

        throw new \RuntimeException('There is no current request.');
    }
}
