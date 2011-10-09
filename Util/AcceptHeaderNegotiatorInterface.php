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

interface AcceptHeaderNegotiatorInterface
{
    /**
     * Detect the request format based on the priorities and the Accept header
     *
     * @param   Request     $request        The request
     * @param   array       $priorities     Ordered array of formats (highest priority first)
     * @param   string      $extension      The request "file" extension
     *
     * @return  null|string                 The format string
     */
    function getBestFormat(Request $request, array $priorities = null, $extension = null);
}
