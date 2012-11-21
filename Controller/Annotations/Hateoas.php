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

use Doctrine\Common\Annotations\Annotation;

/**
 * Hateoas annotation class.
 * @Annotation
 */
class Hateoas extends Annotation
{
    /** @var string */
    public $subject;
    /** @var string */
    public $identifier = 'id';
    /** @var string */
    public $relName;
}
