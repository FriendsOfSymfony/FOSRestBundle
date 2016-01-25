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

class ViolationFormatter implements ViolationFormatterInterface
{
    /**
     * {@inheritdoc}
     */
    public function format(ParamInterface $param, ConstraintViolationInterface $violation)
    {
        return sprintf(
            "Parameter %s value '%s' violated a constraint (%s)",
            $param->getName(),
            $violation->getInvalidValue(),
            $violation->getMessage()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function formatList(ParamInterface $param, ConstraintViolationListInterface $violationList)
    {
        $str = '';
        foreach ($violationList as $key => $violation) {
            if ($key > 0) {
                $str .= "\n";
            }
            $str .= $this->format($param, $violation);
        }

        return $str;
    }
}
