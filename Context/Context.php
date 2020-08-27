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
    private $attributes = [];
    private $version;
    private $groups;
    private $maxDepth;
    private $isMaxDepthEnabled;
    private $serializeNull;

    /**
     * @var ExclusionStrategyInterface[]
     */
    private $exclusionStrategies = [];

    public function setAttribute(string $key, $value): self
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    public function hasAttribute(string $key): bool
    {
        return isset($this->attributes[$key]);
    }

    public function getAttribute(string $key)
    {
        if (isset($this->attributes[$key])) {
            return $this->attributes[$key];
        }
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param string|null $version
     */
    public function setVersion($version): self
    {
        if (is_int($version)) {
            @trigger_error(sprintf('Passing integers as version numbers to %s() is deprecated since FOSRestBundle 2.8. Starting with 3.0 strings will be enforced.', __METHOD__), E_USER_DEPRECATED);
        }

        $this->version = $version;

        return $this;
    }

    /**
     * @return string|int|null
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Adds a normalization group.
     */
    public function addGroup(string $group): self
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
     */
    public function addGroups(array $groups): self
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
    public function getGroups(): ?array
    {
        return $this->groups;
    }

    /**
     * Set the normalization groups.
     *
     * @param string[]|null $groups
     */
    public function setGroups(array $groups = null): self
    {
        $this->groups = $groups;

        return $this;
    }

    /**
     * Sets the normalization max depth.
     *
     * @deprecated since 2.1, to be removed in 3.0. Use {@link self::enableMaxDepth()} and {@link self::disableMaxDepth()} instead
     */
    public function setMaxDepth(?int $maxDepth): self
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
     * @deprecated since version 2.1, to be removed in 3.0. Use {@link self::isMaxDepthEnabled()} instead
     */
    public function getMaxDepth(): ?int
    {
        if (0 === func_num_args() || func_get_arg(0)) {
            @trigger_error(sprintf('%s is deprecated since version 2.1 and will be removed in 3.0. Use %s::isMaxDepthEnabled() instead.', __METHOD__, __CLASS__), E_USER_DEPRECATED);
        }

        return $this->maxDepth;
    }

    public function enableMaxDepth(): self
    {
        $this->isMaxDepthEnabled = true;

        return $this;
    }

    public function disableMaxDepth(): self
    {
        $this->isMaxDepthEnabled = false;

        return $this;
    }

    public function isMaxDepthEnabled(): ?bool
    {
        return $this->isMaxDepthEnabled;
    }

    public function setSerializeNull(?bool $serializeNull): self
    {
        $this->serializeNull = $serializeNull;

        return $this;
    }

    public function getSerializeNull(): ?bool
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
    public function getExclusionStrategies(): array
    {
        return $this->exclusionStrategies;
    }

    /**
     * Adds an exclusion strategy.
     *
     * Notice: This method only applies to the JMS serializer adapter.
     */
    public function addExclusionStrategy(ExclusionStrategyInterface $exclusionStrategy)
    {
        $this->exclusionStrategies[] = $exclusionStrategy;
    }
}
