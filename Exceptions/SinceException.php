<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Exceptions;


class SinceException extends Exception
{
    /**
     * @param string $version
     * @param int $sinceVersion
     */
    public function __construct($version, $sinceVersion)
    {
        parent::__construct(sprintf("This method doesn't exists into API version %s. Available since version %s", $version, $sinceVersion), 404);
    }
}