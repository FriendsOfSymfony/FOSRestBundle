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

use Symfony\Component\HttpFoundation\Request;

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
