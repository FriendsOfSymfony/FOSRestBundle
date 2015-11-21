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

use Symfony\Component\HttpFoundation\Request;

/**
 * @deprecated since 1.7, to be removed in 2.0.
 */
interface MediaTypeNegotiatorInterface extends FormatNegotiatorInterface
{
    /**
     * Gets the best media type.
     *
     * @param Request $request The request
     *
     * @return null|string
     */
    public function getBestMediaType(Request $request);
}
