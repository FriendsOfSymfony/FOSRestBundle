<?php

namespace FOS\RestBundle\Controller\Annotations;

use Symfony\Component\Routing\Annotation\Route as BaseRoute;

use Doctrine\Common\Annotations\Annotation;

/*
 * This file is part of the FOS/RestBundle
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

/**
 * No Route annotation class
 */
class NoRoute extends BaseRoute
{
}

/**
 * GET Route annotation class.
 */
class Get extends Route
{
    public function getMethod()
    {
        return 'GET';
    }
}

/**
 * POST Route annotation class.
 */
class Post extends Route
{
    public function getMethod()
    {
        return 'POST';
    }
}

/**
 * PUT Route annotation class.
 */
class Put extends Route
{
    public function getMethod()
    {
        return 'PUT';
    }
}

/**
 * DELETE Route annotation class.
 */
class Delete extends Route
{
    public function getMethod()
    {
        return 'DELETE';
    }
}

/**
 * HEAD Route annotation class.
 */
class Head extends Route
{
    public function getMethod()
    {
        return 'HEAD';
    }
}

class Prefix extends Annotation
{
}

class NamePrefix extends Annotation
{
}
