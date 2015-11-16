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
interface SerializeNullContextInterface extends ContextInterface
{
    /**
     * Sets serialize null.
     *
     * @param null|bool $serializeNull
     *
     * @return SerializeNullContextInterface
     */
    public function setSerializeNull($serializeNull);

    /**
     * Gets serialize null.
     *
     * @return null|bool
     */
    public function getSerializeNull();
}
