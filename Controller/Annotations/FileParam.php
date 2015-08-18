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
 * Represents a file that must be present.
 *
 * @Annotation
 * @Target("METHOD")
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
class FileParam extends Param
{
    /** @var bool */
    public $strict = true;
    /** @var bool */
    public $image = false;
}
