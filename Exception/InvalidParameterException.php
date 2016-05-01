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

use FOS\RestBundle\Controller\Annotations\Param;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class InvalidParameterException extends BadRequestHttpException
{
    private $parameter;
    private $violations;

    public function __construct(Param $parameter, ConstraintViolationListInterface $violations)
    {
        $this->parameter = $parameter;
        $this->violations = $violations;

        $message = '';
        foreach ($violations as $key => $violation) {
            if ($key > 0) {
                $message .= "\n";
            }
            $message .= sprintf(
                'Parameter "%s" of value "%s" violated a constraint "%s"',
                $parameter->name,
                $violation->getInvalidValue(),
                $violation->getMessage()
            );
        }
        parent::__construct($message);
    }

    public function getParameter()
    {
        return $this->parameter;
    }

    public function getViolations()
    {
        return $this->violations;
    }
}
