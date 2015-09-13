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
 * Context with max depth.
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
interface MaxDepthContextInterface extends ContextInterface
{
    /**
     * Sets the normalization max depth.
     *
     * @param null|int $depth
     *
     * @return MaxDepthContextInterface
     */
    public function setMaxDepth($depth);

    /**
     * Gets the normalization max depth.
     *
     * @return null|int
     */
    public function getMaxDepth();
}
