<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Routing;

/**
 * Hateoas
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
interface HateoasCollectionInterface
{
    /**
     * Get subject class
     *
     * @return string
     */
    public function getSubject();
}
