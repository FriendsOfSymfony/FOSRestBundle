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

use FOS\RestBundle\Negotiation\FormatNegotiator as BaseFormatNegotiator;
use Symfony\Component\HttpFoundation\Request;

/**
 * @deprecated since 1.7, to be removed in 2.0. Use {@link \FOS\RestBundle\Negotiation\FormatNegotiator} instead.
 */
class FormatNegotiator implements MediaTypeNegotiatorInterface
{
    private $negotiator;

    public function __construct()
    {
        @trigger_error('FOS\RestBundle\Util\FormatNegotiator is deprecated since version 1.7 and will be removed in 2.0. Use FOS\RestBundle\Negotiation\FormatNegotiator instead.', E_USER_DEPRECATED);

        $this->negotiator = new BaseFormatNegotiator();
    }

    /**
     * {@inheritdoc}
     */
    public function getBestFormat(Request $request)
    {
        return $this->negotiator->getBestFormat($request);
    }

    /**
     * {@inheritdoc}
     */
    public function getBestMediaType(Request $request)
    {
        return $this->negotiator->getBestMediaType($request);
    }

    public function __call($method, $args)
    {
        if (!method_exists($this->negotiator, $method)) {
            throw new \BadMethodCallException(sprintf('Call to undefined method %s::%s()', get_class($this), $method));
        }

        return call_user_func_array(array($this->negotiator, $method), $args);
    }
}
