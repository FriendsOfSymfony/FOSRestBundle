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

use JMS\Serializer\Exclusion\ExclusionStrategyInterface;

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
     * @var array|null
     */
    private $groups;
    /**
     * @var int
     */
    private $maxDepth;
    /**
     * @var bool
     */
    private $isMaxDepthEnabled;
    /**
     * @var bool
     */
    private $serializeNull;
    /**
     * @var ExclusionStrategyInterface[]
     */
    private $exclusionStrategies = array();

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
        if (null === $this->groups) {
            $this->groups = [];
        }
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
     * @return string[]|null
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Set the normalization groups.
     *
     * @param string[]|null $groups
     *
     * @return self
     */
    public function setGroups(array $groups = null)
    {
        $this->groups = $groups;

        return $this;
    }

    /**
     * Sets the normalization max depth.
     *
     * @param int|null $maxDepth
     *
     * @return self
     *
     * @deprecated since 2.1, to be removed in 3.0. Use {@link self::enableMaxDepth()} and {@link self::disableMaxDepth()} instead
     */
    public function setMaxDepth($maxDepth)
    {
        if (1 === func_num_args() || func_get_arg(1)) {
            @trigger_error(sprintf('%s is deprecated since version 2.1 and will be removed in 3.0. Use %s::enableMaxDepth() and %s::disableMaxDepth() instead.', __METHOD__, __CLASS__, __CLASS__), E_USER_DEPRECATED);
        }
        $this->maxDepth = $maxDepth;

        return $this;
    }

    /**
     * Gets the normalization max depth.
     *
     * @return int|null
     *
     * @deprecated since version 2.1, to be removed in 3.0. Use {@link self::isMaxDepthEnabled()} instead
     */
    public function getMaxDepth()
    {
        if (0 === func_num_args() || func_get_arg(0)) {
            @trigger_error(sprintf('%s is deprecated since version 2.1 and will be removed in 3.0. Use %s::isMaxDepthEnabled() instead.', __METHOD__, __CLASS__), E_USER_DEPRECATED);
        }

        return $this->maxDepth;
    }

    public function enableMaxDepth()
    {
        $this->isMaxDepthEnabled = true;

        return $this;
    }

    public function disableMaxDepth()
    {
        $this->isMaxDepthEnabled = false;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function isMaxDepthEnabled()
    {
        return $this->isMaxDepthEnabled;
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

    /**
     * Gets the array of exclusion strategies.
     *
     * Notice: This method only applies to the JMS serializer adapter.
     *
     * @return ExclusionStrategyInterface[]
     */
    public function getExclusionStrategies()
    {
        return $this->exclusionStrategies;
    }

    /**
     * Adds an exclusion strategy.
     *
     * Notice: This method only applies to the JMS serializer adapter.
     *
     * @param ExclusionStrategyInterface $exclusionStrategy
     */
    public function addExclusionStrategy(ExclusionStrategyInterface $exclusionStrategy)
    {
        $this->exclusionStrategies[] = $exclusionStrategy;
    }
}
