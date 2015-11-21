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

use FOS\RestBundle\Validator\ViolationFormatter as BaseViolationFormatter;

@trigger_error(__NAMESPACE__.'\ViolationFormatter is deprecated since version 1.7 and will be removed in 2.0. Use FOS\RestBundle\Validator\ViolationFormatter instead.');

/**
 * @deprecated since 1.7, to be removed in 2.0. Use {@link \FOS\RestBundle\Validator\ViolationFormatter} instead.
 */
class ViolationFormatter extends BaseViolationFormatter implements ViolationFormatterInterface
{
}
