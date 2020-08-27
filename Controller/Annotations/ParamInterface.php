<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Controller\Annotations;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraint;

/**
 * Represents a parameter that can be present in the request attributes.
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
interface ParamInterface
{
    /**
     * Get param name.
     *
     * @return string
     */
    public function getName();

    /**
     * @return mixed
     */
    public function getDefault();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * Get incompatibles parameters.
     *
     * @return array
     */
    public function getIncompatibilities();

    /**
     * @return Constraint[]
     */
    public function getConstraints();

    /**
     * @return bool
     */
    public function isStrict();

    /**
     * Get param value in function of the current request.
     *
     * @param mixed $default value
     *
     * @return mixed
     */
    public function getValue(Request $request, $default);
}
