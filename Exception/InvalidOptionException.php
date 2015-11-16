<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Exception;

class InvalidOptionException extends \RuntimeException
{
    private $options;

    public function __construct($option, $class)
    {
        parent::__construct(
            sprintf('The option "%s" does not exist in %s', $option, $class)
        );
    }
}
