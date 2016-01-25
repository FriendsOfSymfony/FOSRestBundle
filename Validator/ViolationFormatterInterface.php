<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Validator;

use FOS\RestBundle\Controller\Annotations\ParamInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

interface ViolationFormatterInterface
{
    /**
     * Format a ParamInterface's ConstraintViolation into a string message.
     *
     * @param ParamInterface               $param
     * @param ConstraintViolationInterface $violation
     *
     * @return string
     */
    public function format(ParamInterface $param, ConstraintViolationInterface $violation);

    /**
     * Format a ParamInterface's ConstraintViolationList into a string message.
     *
     * @param ParamInterface                   $param
     * @param ConstraintViolationListInterface $violationList
     *
     * @return string
     */
    public function formatList(ParamInterface $param, ConstraintViolationListInterface $violationList);
}
