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
use FOS\RestBundle\Controller\Annotations\ParamInterface;
use FOS\RestBundle\Util\ResolverTrait;
use FOS\RestBundle\Validator\Constraints\ResolvableConstraintInterface;
use FOS\RestBundle\Validator\ViolationFormatterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\ConstraintViolation;

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
    use ResolverTrait;

    private $container;
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
     * @param ContainerInterface          $container
     * @param ParamReaderInterface        $paramReader
     * @param RequestStack                $requestStack
     * @param ValidatorInterface          $validator
     * @param ViolationFormatterInterface $violationFormatter
     */
    public function __construct(ContainerInterface $container, ParamReaderInterface $paramReader, RequestStack $requestStack, ViolationFormatterInterface $violationFormatter, ValidatorInterface $validator = null)
    {
        $this->container = $container;
        $this->paramReader = $paramReader;
        $this->requestStack = $requestStack;
        $this->violationFormatter = $violationFormatter;
        $this->validator = $validator;
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
     * @param mixed          $paramValue
     * @param bool           $strict
     *
     * @throws BadRequestHttpException
     * @throws \RuntimeException
     *
     * @return mixed
     */
    protected function cleanParamWithRequirements(ParamInterface $param, $paramValue, $strict)
    {
        $default = $param->getDefault();
        $default = $this->resolveValue($this->container, $default);

        $this->checkNotIncompatibleParams($param);
        if ($default !== null && $default === $paramValue) {
            return $paramValue;
        }

        $constraints = $param->getConstraints();
        $this->resolveConstraints($constraints);
        if (empty($constraints)) {
            return $paramValue;
        }
        if (null === $this->validator) {
            throw new \RuntimeException(
                'The ParamFetcher requirements feature requires the symfony/validator component.'
            );
        }

        try {
            $errors = $this->validator->validate($paramValue, $constraints);
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

        $params = [];
        foreach ($configuredParams as $name => $param) {
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
     * @param Constraint[] $constraints
     */
    private function resolveConstraints(array $constraints)
    {
        foreach ($constraints as $constraint) {
            if ($constraint instanceof ResolvableConstraintInterface) {
                $constraint->resolve($this->container);
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
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            throw new \RuntimeException('There is no current request.');
        }

        return $request;
    }
}
