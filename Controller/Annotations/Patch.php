<?php

namespace FOS\RestBundle\Controller\Annotations;

/*
 * This file is part of the FOSRestBundle
 *
 * (c) Lukas Kahwe Smith <smith@pooteeweet.org>
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 * (c) Bulat Shakirzyanov <mallluhuct@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * PATCH Route annotation class.
 */
class Patch extends Route
{
    public function getMethod()
    {
        return 'PATCH';
    }
}