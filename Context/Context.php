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
final class Context
{
    /**
     * @var array
     */
    private $attributes = array();
    /**
     * @var int|null
     */
    private $version;
    /**
     * @var array
     */
    private $groups = array();
    /**
     * @var int
     */
    private $maxDepth;
    /**
     * @var bool
     */
    private $serializeNull;

    /**
     * Sets an attribute.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return self
     */
    public function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Checks if contains a normalization attribute.
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasAttribute($key)
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Gets an attribute.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (isset($this->attributes[$key])) {
            return $this->attributes[$key];
        }
    }

    /**
     * Gets the attributes.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Sets the normalization version.
     *
     * @param int|null $version
     *
     * @return self
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Gets the normalization version.
     *
     * @return int|null
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Adds a normalization group.
     *
     * @param string $group
     *
     * @return self
     */
    public function addGroup($group)
    {
        if (!in_array($group, $this->groups)) {
            $this->groups[] = $group;
        }

        return $this;
    }

    /**
     * Adds normalization groups.
     *
     * @param string[] $groups
     *
     * @return self
     */
    public function addGroups(array $groups)
    {
        foreach ($groups as $group) {
            $this->addGroup($group);
        }

        return $this;
    }

    /**
     * Gets the normalization groups.
     *
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Sets the normalization max depth.
     *
     * @param int|null $depth
     *
     * @return self
     */
    public function setMaxDepth($maxDepth)
    {
        $this->maxDepth = $maxDepth;

        return $this;
    }

    /**
     * Gets the normalization max depth.
     *
     * @return int|null
     */
    public function getMaxDepth()
    {
        return $this->maxDepth;
    }

    /**
     * Sets serialize null.
     *
     * @param bool|null $serializeNull
     *
     * @return self
     */
    public function setSerializeNull($serializeNull)
    {
        $this->serializeNull = $serializeNull;

        return $this;
    }

    /**
     * Gets serialize null.
     *
     * @return bool|null
     */
    public function getSerializeNull()
    {
        return $this->serializeNull;
    }
}
