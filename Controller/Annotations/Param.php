<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Controller\Annotations;

/**
 * Represents a parameter that can be present in GET or POST data.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
abstract class Param
{
    /** @var string */
    public $name;
    /** @var string */
    public $requirements = '';
    /** @var string */
    public $default = '';
    /** @var string */
    public $description;
    /** @var string */
    public $strict = false;
    /** @var boolean */
    public $array = false;
}
