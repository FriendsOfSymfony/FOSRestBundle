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


class UntilException extends Exception
{
    /**
     * @param string $version
     * @param int $untilVersion
     */
    public function __construct($version, $untilVersion)
    {
        parent::__construct(sprintf("This method doesn't exists anymore into API version %s. Available until version %s", $version, $untilVersion), 404);
    }
}