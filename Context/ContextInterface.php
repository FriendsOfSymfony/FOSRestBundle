<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Context;

/**
 * Stores the serialization or deserialization context (groups, version, ...).
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
interface ContextInterface
{
    /**
     * Sets a normalization attribute.
     *
     * @param mixed $key
     * @param mixed $value
     *
     * @return ContextInterface
     */
    public function setAttribute($key, $value);

    /**
     * Checks if contains a normalization attribute.
     *
     * @param mixed $key
     *
     * @return bool
     */
    public function hasAttribute($key);

    /**
     * Gets a normalization attribute.
     *
     * @param mixed $key
     *
     * @return mixed
     */
    public function getAttribute($key);

    /**
     * Gets the normalization attributes.
     *
     * @return array
     */
    public function getAttributes();
}
