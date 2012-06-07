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
 * QueryParam annotation class.
 *
 * @Annotation
 * @author Alexander <iam.asm89@gmail.com>
 */
class QueryParam
{
    /** @var string */
    public $name;
    /** @var string */
    public $requirements = '';
    /** @var string */
    public $default = '';
    /** @var string */
    public $description;
}
