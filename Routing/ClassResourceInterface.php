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

@trigger_error(sprintf('The %s\ClassResourceInterface is deprecated since FOSRestBundle 2.8.', __NAMESPACE__), E_USER_DEPRECATED);

/**
 * Implement interface to define that missing resources in the methods should
 * use the class name to identify the resource.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 *
 * @deprecated since 2.8
 */
interface ClassResourceInterface
{
}
