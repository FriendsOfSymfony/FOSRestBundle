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

/**
 * @deprecated since version 1.8 and will be removed in 2.0. Catch specialized exception classes instead of relying on specific exception messages.
 * @see FOS\RestBundle\Exception\InvalidParameterException
 */
class ViolationFormatter implements ViolationFormatterInterface
{
    private static $deprecationTriggered = false;

    public function __construct($triggerDeprecation = true)
    {
        if ($triggerDeprecation && !static::$deprecationTriggered) {
            @trigger_error(sprintf('The %s class is deprecated since version 1.8 and will be removed in 2.0. Catch specialized exception classes instead of relying on specific exception messages.', __CLASS__), E_USER_DEPRECATED);
            static::$deprecationTriggered = true;
        }
    }

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
