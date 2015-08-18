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
use Symfony\Component\Validator\Constraint;
use FOS\RestBundle\Controller\Annotations\Param;
use FOS\RestBundle\Controller\Annotations\ScalarParam;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use FOS\RestBundle\Validator\ViolationFormatterInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;
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
class ParamFetcher extends ContainerAware implements ParamFetcherInterface
{
    private $paramReader;
    private $requestStack;
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
     * @param ParamReader                                 $paramReader
     * @param RequestStack                                $requestStack
     * @param ValidatorInterface|LegacyValidatorInterface $validator
     * @param ViolationFormatterInterface                 $violationFormatter
     */
    public function __construct(ParamReader $paramReader, RequestStack $requestStack, ViolationFormatterInterface $violationFormatter, $validator = null)
    {
        $this->paramReader = $paramReader;
        $this->requestStack = $requestStack;
        $this->violationFormatter = $violationFormatter;
        $this->validator = $validator;

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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function get($name, $strict = null)
    {
        $params = $this->getParams();

        if (!array_key_exists($name, $params)) {
            throw new \InvalidArgumentException(sprintf("No @QueryParam/@RequestParam/@FileParam configuration for parameter '%s'.", $name));
        }

        /** @var Param $config */
        $config = $params[$name];
        $nullable = $config->nullable;
        $default = $config->default;
        $paramType = $config instanceof QueryParam ? 'Query' : ($config instanceof FileParam ? 'File' : 'Request');

        if (null === $strict) {
            $strict = $config->strict;
        }

        if ($config instanceof RequestParam) {
            $param = $this->requestStack->getCurrentRequest()->request->get($config->getKey(), $default);
        } elseif ($config instanceof QueryParam) {
            $param = $this->requestStack->getCurrentRequest()->query->get($config->getKey(), $default);
        } elseif ($config instanceof FileParam) {
            $param = $this->requestStack->getCurrentRequest()->files->get($config->getKey(), $default);
        } else {
            $param = null;
        }

        if ($config instanceof ScalarParam && $config->array) {
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

        if ((!($config instanceof FileParam) && !is_scalar($param)) || ($config instanceof FileParam && !($param instanceof UploadedFile))) {
            if (!$nullable) {
                if ($strict) {
                    if ($config instanceof FileParam) {
                        $problem = 'not a file';
                    } else {
                        $problem = empty($param) ? 'empty' : 'not a scalar';
                    }

                    throw new BadRequestHttpException(
                        sprintf('%s parameter "%s" is %s', $paramType, $name, $problem)
                    );
                }
            } else {
                return $default;
            }
        }

        return $this->cleanParamWithRequirements($config, $param, $strict);
    }

    /**
     * @param Param  $config
     * @param string $param
     * @param bool   $strict
     *
     * @throws BadRequestHttpException
     * @throws \RuntimeException
     *
     * @return string
     */
    public function cleanParamWithRequirements(Param $config, $param, $strict)
    {
        $default = $config->default;
        $paramType = $config instanceof QueryParam ? 'Query' : ($config instanceof FileParam ? 'File' : 'Request');

        if (null !== $config->requirements && null === $this->validator) {
            throw new \RuntimeException(
                'The ParamFetcher requirements feature requires the symfony/validator component.'
            );
        }

        $this->checkNotIncompatibleParams($config);

        if (null === $config->requirements || ($param === $default && null !== $default)) {
            return $param;
        }

        $constraints = array();

        if ($config->requirements instanceof Constraint) { // Complex requirements
            $constraints[] = $config->requirements;
        } elseif ($config instanceof FileParam) { // FileParam constraints
            if (is_array($config->requirements)) {
                if ($config->image) {
                    $constraints[] = new Image($config->requirements);
                } else {
                    $constraints[] = new File($config->requirements);
                }
            }
        } else { // Other params
            if (is_scalar($config->requirements)) {
                if (is_array($param)) {
                    if ($strict) {
                        throw new BadRequestHttpException(
                            sprintf('%s parameter is an array', $paramType)
                        );
                    }

                    return $default;
                }
                $constraints[] = new Regex(array(
                    'pattern' => '#^(?:'.$config->requirements.')$#xsu',
                    'message' => sprintf(
                        "%s parameter value '%s', does not match requirements '%s'",
                        $paramType,
                        $param,
                        $config->requirements
                    ),
                ));
            } elseif (is_array($config->requirements) && isset($config->requirements['rule']) && $config->requirements['error_message']) {
                $constraints[] = new Regex(array(
                    'pattern' => '#^(?:'.$config->requirements['rule'].')$#xsu',
                    'message' => $config->requirements['error_message'],
                ));
            }

            if ($config instanceof ScalarParam && false === $config->allowBlank) {
                $constraints[] = new NotBlank();
            }
        }

        if ($this->validator instanceof ValidatorInterface) {
            $errors = $this->validator->validate($param, $constraints);
        } else {
            $errors = $this->validator->validateValue($param, $constraints);
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

        $params = [];
        foreach ($configuredParams as $name => $config) {
            $params[$name] = $this->get($name, $strict);
        }

        return $params;
    }

    /**
     * Initialize the parameters.
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

        $params = $this->paramReader->read(
            new \ReflectionClass(ClassUtils::getClass($this->controller[0])),
            $this->controller[1]
        );
        $this->resolveParameters($params);

        $this->params = $params;
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
            $isIncompatiblePresent = $this->requestStack->getCurrentRequest()->query->get(
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
     * @param Param[] $params
     */
    private function resolveParameters(array $params)
    {
        foreach ($params as $param) {
            $param->requirements = $this->resolve($param->requirements);
            $param->default = $this->resolve($param->default);
        }
    }

    private function resolve($value)
    {
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = $this->resolve($val);
            }

            return $value;
        }

        if (!is_string($value)) {
            return $value;
        }

        $container = $this->container;

        $escapedValue = preg_replace_callback('/%%|%([^%\s]++)%/', function ($match) use ($container, $value) {
            // skip %%
            if (!isset($match[1])) {
                return '%%';
            }

            if (empty($container)) {
                throw new \InvalidArgumentException(
                    'The ParamFetcher has been not initialized correctly. '.
                    'The container for parameter resolution is missing.'
                );
            }

            $resolved = $container->getParameter($match[1]);
            if (is_string($resolved) || is_numeric($resolved)) {
                return (string) $resolved;
            }

            throw new RuntimeException(sprintf(
                    'The container parameter "%s", used in the controller parameters '.
                    'configuration value "%s", must be a string or numeric, but it is of type %s.',
                    $match[1],
                    $value,
                    gettype($resolved)
                )
            );
        }, $value);

        return str_replace('%%', '%', $escapedValue);
    }
}
