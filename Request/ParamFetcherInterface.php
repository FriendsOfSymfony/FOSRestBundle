<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Request;

use Symfony\Component\HttpFoundation\Request;

/**
 * Helper interface to validate query parameters from the active request.
 *
 * @author Alexander <iam.asm89@gmail.com>
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
interface ParamFetcherInterface
{
    /**
     * @param callable $controller
     *
     * @return void
     */
    public function setController($controller);

    /**
     * Get a validated parameter.
     *
     * @param string  $name   Name of the parameter
     * @param Boolean $strict Whether a requirement mismatch should cause an exception
     *
     * @return mixed Value of the parameter.
     */
    public function get($name, $strict = null);

    /**
     * Get all validated parameter.
     *
     * @param Boolean $strict Whether a requirement mismatch should cause an exception
     *
     * @return array Values of all the parameters.
     */
    public function all($strict = false);
}
