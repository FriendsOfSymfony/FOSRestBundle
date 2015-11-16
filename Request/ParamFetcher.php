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

use FOS\RestBundle\Controller\Annotations\ParamInterface;
use FOS\RestBundle\Validator\ViolationFormatterInterface;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
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
     * @param ParamReaderInterface                        $paramReader
     * @param Request|RequestStack                        $request
     * @param ValidatorInterface|LegacyValidatorInterface $validator
     * @param ViolationFormatterInterface                 $violationFormatter
     */
    public function __construct(ParamReaderInterface $paramReader, $requestStack, ViolationFormatterInterface $violationFormatter, $validator = null)
    {
        $this->paramReader = $paramReader;
        if (!($requestStack instanceof Request) && !($requestStack instanceof RequestStack)) {
            throw new \InvalidArgumentException('$requestStack must be an instance of Symfony\Component\HttpFoundation\Request or Symfony\Component\HttpFoundation\RequestStack.');
        }
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
     * @param ParamInterface $param
     */
    public function addParam(ParamInterface $param)
    {
        $this->getParams(); // init params
        $this->params[$param->getName()] = $param;
    }

    /**
     * @return ParamInterface[]
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
            throw new \InvalidArgumentException(sprintf("No @ParamInterface configuration for parameter '%s'.", $name));
        }

        /** @var ParamInterface $param */
        $param = $params[$name];

        $default = $param->getDefault();
        $strict = (null !== $strict ? $strict : $param->isStrict());

        $paramValue = $param->getValue($this->getRequest(), $default);

        return $this->cleanParamWithRequirements($param, $paramValue, $strict);
    }

    /**
     * @param ParamInterface $param
     * @param string         $paramValue
     * @param bool           $strict
     *
     * @return string
     *
     * @throws BadRequestHttpException
     * @throws \RuntimeException
     *
     * @deprecated since 1.7, will be private in 2.0.
     */
    public function cleanParamWithRequirements(ParamInterface $param, $paramValue, $strict)
    {
        $default = $param->getDefault();

        $this->checkNotIncompatibleParams($param);
        if ($default !== null && $default === $paramValue) {
            return $paramValue;
        }

        $constraints = $param->getConstraints();
        if (empty($constraints)) {
            return $paramValue;
        }
        if (null === $this->validator) {
            throw new \RuntimeException(
                'The ParamFetcher requirements feature requires the symfony/validator component.'
            );
        }

        try {
            if ($this->validator instanceof ValidatorInterface) {
                $errors = $this->validator->validate($paramValue, $constraints);
            } else {
                $errors = $this->validator->validateValue($paramValue, $constraints);
            }
        } catch (ValidatorException $e) {
            $violation = new ConstraintViolation(
                $e->getMessage(),
                $e->getMessage(),
                array(),
                $paramValue,
                '',
                null,
                null,
                $e->getCode()
            );
            $errors = new ConstraintViolationList(array($violation));
        }

        if (0 < count($errors)) {
            if ($strict) {
                throw new BadRequestHttpException(
                    $this->violationFormatter->formatList($param, $errors)
                );
            }

            return null === $default ? '' : $default;
        }

        return $paramValue;
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
     * Initialize the parameters.
     *
     * @throws \InvalidArgumentException
     */
    private function initParams()
    {
        if (empty($this->controller)) {
            throw new \InvalidArgumentException('Controller and method needs to be set via setController');
        }

        if (!is_array($this->controller) || empty($this->controller[0]) || empty($this->controller[1])) {
            throw new \InvalidArgumentException(
                'Controller needs to be set as a class instance (closures/functions are not supported)'
            );
        }

        // the controller could be a proxy, e.g. when using the JMSSecuriyExtraBundle or JMSDiExtraBundle
        $className = ClassUtils::getClass($this->controller[0]);

        $this->params = $this->paramReader->read(
            new \ReflectionClass($className),
            $this->controller[1]
        );
    }

    /**
     * Check if current param is not in conflict with other parameters
     * according to the "incompatibles" field.
     *
     * @param ParamInterface $param the configuration for the param fetcher
     *
     * @throws InvalidArgumentException
     * @throws BadRequestHttpException
     */
    protected function checkNotIncompatibleParams(ParamInterface $param)
    {
        $params = $this->getParams();
        foreach ($param->getIncompatibilities() as $incompatibleParamName) {
            if (!array_key_exists($incompatibleParamName, $params)) {
                throw new \InvalidArgumentException(sprintf("No @ParamInterface configuration for parameter '%s'.", $incompatibleParamName));
            }
            $incompatibleParam = $params[$incompatibleParamName];

            if ($incompatibleParam->getValue($this->getRequest(), null) !== null) {
                $exceptionMessage = sprintf(
                    "'%s' param is incompatible with %s param.",
                    $param->getName(),
                    $incompatibleParam->getName()
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
        }

        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            throw new \RuntimeException('There is no current request.');
        }

        return $request;
    }
}
