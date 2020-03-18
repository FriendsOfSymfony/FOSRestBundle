<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Exception;

use FOS\RestBundle\Controller\Annotations\ParamInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class InvalidParameterException extends BadRequestHttpException
{
    private $parameter;
    private $violations;

    public function getParameter()
    {
        return $this->parameter;
    }

    public function getViolations()
    {
        return $this->violations;
    }

    public static function withViolations(ParamInterface $parameter, ConstraintViolationListInterface $violations)
    {
        $message = '';

        foreach ($violations as $key => $violation) {
            if ($key > 0) {
                $message .= "\n";
            }

            $invalidValue = $violation->getInvalidValue();

            $message .= sprintf(
                'Parameter "%s" of value "%s" violated a constraint "%s"',
                $parameter->getName(),
                is_scalar($invalidValue) ? $invalidValue : var_export($invalidValue, true),
                $violation->getMessage()
            );
        }

        return self::withViolationsAndMessage($parameter, $violations, $message);
    }

    /**
     * Do not use this method. It will be removed in 2.0.
     *
     * @internal
     */
    public static function withViolationsAndMessage(ParamInterface $parameter, ConstraintViolationListInterface $violations, string $message): self
    {
        $exception = new self($message);
        $exception->parameter = $parameter;
        $exception->violations = $violations;

        return $exception;
    }
}
