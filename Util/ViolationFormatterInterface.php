<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Util;

use FOS\RestBundle\Controller\Annotations\Param;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @deprecated since version 1.8 and will be removed in 2.0. Catch specialized exception classes instead of relying on specific exception messages.
 * @see FOS\RestBundle\Exception\InvalidParameterException
 */
interface ViolationFormatterInterface
{
    /**
     * Format a Param's ConstraintViolation into a string message.
     *
     * @param Param                        $param
     * @param ConstraintViolationInterface $violation
     *
     * @return string
     */
    public function format(Param $param, ConstraintViolationInterface $violation);

    /**
     * Format a Param's ConstraintViolationList into a string message.
     *
     * @param Param                            $param
     * @param ConstraintViolationListInterface $violationList
     *
     * @return string
     */
    public function formatList(Param $param, ConstraintViolationListInterface $violationList);
}
