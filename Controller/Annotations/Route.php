<?php

namespace FOS\RestBundle\Controller\Annotations;

use Symfony\Component\Routing\Annotation\Route as BaseRoute;

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
 * Route annotation class.
 */
class Route extends BaseRoute
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $requirements = $this->getRequirements();
        $requirements['_method'] = $this->getMethod();
        $this->setRequirements($requirements);
    }

    public function getMethod()
    {
        return null;
    }
}
