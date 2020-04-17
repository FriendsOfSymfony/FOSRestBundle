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

@trigger_error(sprintf('The %s\RouteResource annotation is deprecated since FOSRestBundle 2.8.', __NAMESPACE__), E_USER_DEPRECATED);

/**
 * RouteResource annotation class.
 *
 * @Annotation
 * @Target("CLASS")
 *
 * @deprecated since 2.8
 */
class RouteResource
{
    /**
     * @var string required
     */
    public $resource;

    /**
     * @var bool
     */
    public $pluralize = true;
}
