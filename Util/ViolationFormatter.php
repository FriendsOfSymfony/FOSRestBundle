<?php

/*
 * This file is part of the FOSRest package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Util;

use FOS\RestBundle\Validator\ViolationFormatter as BaseViolationFormatter;

/**
 * @deprecated since version 1.7, to be removed in 2.0. Use {@link BaseViolationFormatter} instead.
 */
class ViolationFormatter extends BaseViolationFormatter implements ViolationFormatterInterface
{
}
