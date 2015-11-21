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
 * {@inheritdoc}
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
class Context implements ContextInterface, GroupableContextInterface, VersionableContextInterface, MaxDepthContextInterface, SerializeNullContextInterface
{
    /**
     * @var array
     */
    protected $attributes;
    /**
     * @var int|null
     */
    protected $version;
    /**
     * @var array
     */
    protected $groups;
    /**
     * @var int
     */
    protected $maxDepth;
    /**
     * @var bool
     */
    protected $serializeNull;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->attributes = [];
        $this->groups = [];
    }

    /**
     * {@inheritdoc}
     */
    public function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttribute($key)
    {
        return isset($this->attributes[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute($key)
    {
        if (isset($this->attributes[$key])) {
            return $this->attributes[$key];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * {@inheritdoc}
     */
    public function addGroup($group)
    {
        if (!is_string($group)) {
            throw new \InvalidArgumentException('A normalization group must be a string.');
        }
        if (!in_array($group, $this->groups)) {
            $this->groups[] = $group;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addGroups(array $groups)
    {
        foreach ($groups as $group) {
            $this->addGroup($group);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * {@inheritdoc}
     */
    public function setMaxDepth($maxDepth)
    {
        $this->maxDepth = $maxDepth;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxDepth()
    {
        return $this->maxDepth;
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializeNull($serializeNull)
    {
        $this->serializeNull = $serializeNull;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSerializeNull()
    {
        return $this->serializeNull;
    }
}
