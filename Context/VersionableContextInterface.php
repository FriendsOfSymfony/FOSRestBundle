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
 * Context with version.
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
interface VersionableContextInterface extends ContextInterface
{
    /**
     * Sets the normalization version.
     *
     * @param int|null $version
     *
     * @return VersionableContextInterface
     */
    public function setVersion($version);

    /**
     * Gets the normalization version.
     *
     * @return int
     */
    public function getVersion();
}
