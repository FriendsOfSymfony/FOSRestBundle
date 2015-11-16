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

@trigger_error(__NAMESPACE__.'\FormatNegotiator is deprecated since version 1.7 and will be removed in 2.0. Use FOS\RestBundle\Negotiation\FormatNegotiator instead.');

use FOS\RestBundle\Negotiation\FormatNegotiator as BaseFormatNegotiator;

/**
 * @deprecated since 1.7, to be removed in 2.0. Use {@link \FOS\RestBundle\Negotiation\FormatNegotiator} instead.
 */
class FormatNegotiator extends BaseFormatNegotiator implements MediaTypeNegotiatorInterface
{
}
