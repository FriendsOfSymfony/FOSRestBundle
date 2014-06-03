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
 * Represents a parameter that must be present in POST data.
 *
 * @Annotation
 * @Target("METHOD")
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author Boris Guéry    <guery.b@gmail.com>
 */
class RequestParam extends Param
{
    /** @var bool */
    public $strict = true;
}
