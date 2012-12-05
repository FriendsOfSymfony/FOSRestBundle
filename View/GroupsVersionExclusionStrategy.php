<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\View;

use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Exclusion\ExclusionStrategyInterface;
use JMS\Serializer\Exclusion\VersionExclusionStrategy;
use JMS\Serializer\Exclusion\GroupsExclusionStrategy;

class GroupsVersionExclusionStrategy implements ExclusionStrategyInterface
{
    private $groupExclusion;
    private $versionExclusion;

    public function __construct($groups, $version)
    {
        $this->groupExclusion = new GroupsExclusionStrategy((array) $groups);
        $this->versionExclusion = new VersionExclusionStrategy($version);
    }

    public function shouldSkipClass(ClassMetadata $metadata, $object = null)
    {
        return false;
    }

    public function shouldSkipProperty(PropertyMetadata $metadata, $object = null)
    {
        return $this->groupExclusion->shouldSkipProperty($metadata) || $this->versionExclusion->shouldSkipProperty($metadata);
    }
}