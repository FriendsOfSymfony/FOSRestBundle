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
 * Context with groups.
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
interface GroupableContextInterface extends ContextInterface
{
    /**
     * Add a normalization group.
     *
     * @param string $group
     *
     * @return GroupableContextInterface
     */
    public function addGroup($group);

    /**
     * Add normalization groups.
     *
     * @param array<string> $groups
     *
     * @return GroupableContextInterface
     */
    public function addGroups(array $groups);

    /**
     * Gets the normalization groups.
     *
     * @return array
     */
    public function getGroups();
}
