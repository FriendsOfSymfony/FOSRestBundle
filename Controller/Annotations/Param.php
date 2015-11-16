<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Controller\Annotations;

use Symfony\Component\HttpFoundation\Request;

/**
 * {@inheritdoc}
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author Boris Guéry <guery.b@gmail.com>
 *
 * @deprecated since 1.7, to be removed in 2.0. Use {@link AbstractScalarParam} instead.
 */
abstract class Param extends AbstractScalarParam
{
    /**
     * {@inheritdoc}
     */
    public function getValue(Request $request, $default = null)
    {
        @trigger_error('You must implement ParamInterface::getValue() for your custom parameters.', E_USER_DEPRECATED);
    }
}
