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
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ViolationFormatter implements ViolationFormatterInterface
{
    /**
     * {@inheritdoc}
     */
    public function format(Param $param, ConstraintViolationInterface $violation)
    {
        return sprintf(
            "%s parameter %s value '%s' violated a constraint (%s)",
            $param instanceof QueryParam ? 'Query' : 'Request',
            $param->getKey(),
            $violation->getInvalidValue(),
            $violation->getMessage()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function formatList(Param $param, ConstraintViolationListInterface $violationList)
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
