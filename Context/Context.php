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

    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getVersion(): ?string
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
    public function addExclusionStrategy(ExclusionStrategyInterface $exclusionStrategy): void
    {
        $this->exclusionStrategies[] = $exclusionStrategy;
    }
}
