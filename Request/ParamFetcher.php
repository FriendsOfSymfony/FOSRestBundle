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
use FOS\RestBundle\Exception\InvalidParameterException;
use FOS\RestBundle\Util\ResolverTrait;
use FOS\RestBundle\Validator\Constraints\ResolvableConstraintInterface;
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
final class ParamFetcher implements ParamFetcherInterface
{
    use ResolverTrait;

    private $container;
    private $parameterBag;
    private $requestStack;
    private $validator;

    public function __construct(ContainerInterface $container, ParamReaderInterface $paramReader, RequestStack $requestStack, ValidatorInterface $validator)
    {
        $this->container = $container;
        $this->requestStack = $requestStack;
        $this->validator = $validator;

        $this->parameterBag = new ParameterBag($paramReader);
    }

    /**
     * {@inheritdoc}
     */
    public function setController(callable $controller): void
    {
        $this->parameterBag->setController($this->getRequest(), $controller);
    }

    /**
     * Add additional params to the ParamFetcher during runtime.
     *
     * Note that adding a param that has the same name as an existing param will override that param.
     */
    public function addParam(ParamInterface $param): void
    {
        $this->parameterBag->addParam($this->getRequest(), $param);
    }

    /**
     * @return ParamInterface[]
     */
    public function getParams(): array
    {
        return $this->parameterBag->getParams($this->getRequest());
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $name, ?bool $strict = null)
    {
        $params = $this->getParams();

        if (!array_key_exists($name, $params)) {
            throw new \InvalidArgumentException(sprintf("No @ParamInterface configuration for parameter '%s'.", $name));
        }

        /** @var ParamInterface $param */
        $param = $params[$name];
        $default = $param->getDefault();
        $default = $this->resolveValue($this->container, $default);
        $strict = (null !== $strict ? $strict : $param->isStrict());

        $paramValue = $param->getValue($this->getRequest(), $default);

        return $this->cleanParamWithRequirements($param, $paramValue, $strict, $default);
    }

    private function cleanParamWithRequirements(ParamInterface $param, $paramValue, bool $strict, $default)
    {
        $this->checkNotIncompatibleParams($param);
        if (null !== $default && $default === $paramValue) {
            return $paramValue;
        }

        $constraints = $param->getConstraints();
        $this->resolveConstraints($constraints);
        if (empty($constraints)) {
            return $paramValue;
        }

        try {
            $errors = $this->validator->validate($paramValue, $constraints);
        } catch (ValidatorException $e) {
            $violation = new ConstraintViolation(
                $e->getMessage(),
                $e->getMessage(),
                [],
                $paramValue,
                '',
                null,
                null,
                $e->getCode()
            );
            $errors = new ConstraintViolationList([$violation]);
        }

        if (0 < count($errors)) {
            if ($strict) {
                throw InvalidParameterException::withViolations($param, $errors);
            }

            return null === $default ? '' : $default;
        }

        return $paramValue;
    }

    /**
     * {@inheritdoc}
     */
    public function all(?bool $strict = null): array
    {
        $configuredParams = $this->getParams();

        $params = [];
        foreach ($configuredParams as $name => $param) {
            $params[$name] = $this->get($name, $strict);
        }

        return $params;
    }

    private function checkNotIncompatibleParams(ParamInterface $param): void
    {
        if (null === $param->getValue($this->getRequest(), null)) {
            return;
        }

        $params = $this->getParams();
        foreach ($param->getIncompatibilities() as $incompatibleParamName) {
            if (!array_key_exists($incompatibleParamName, $params)) {
                throw new \InvalidArgumentException(sprintf("No @ParamInterface configuration for parameter '%s'.", $incompatibleParamName));
            }
            $incompatibleParam = $params[$incompatibleParamName];

            if (null !== $incompatibleParam->getValue($this->getRequest(), null)) {
                $exceptionMessage = sprintf(
                    '"%s" param is incompatible with %s param.',
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
    private function resolveConstraints(array $constraints): void
    {
        foreach ($constraints as $constraint) {
            if ($constraint instanceof ResolvableConstraintInterface) {
                $constraint->resolve($this->container);
            }
        }
    }

    private function getRequest(): Request
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            throw new \RuntimeException('There is no current request.');
        }

        return $request;
    }
}
