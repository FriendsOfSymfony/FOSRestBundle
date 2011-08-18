<?php

/*
 * This file is part of the FOSRestBundle
 *
 * (c) Lukas Kahwe Smith <smith@pooteeweet.org>
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 * (c) Bulat Shakirzyanov <avalanche123>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace FOS\RestBundle\Request;

use Symfony\Component\HttpFoundation\Request;

interface ContentNegotiatorInterface
{
    function getBestMediaType(Request $request, array $availableTypes);
}