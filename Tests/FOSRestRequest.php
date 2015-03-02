<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests;

use FOS\RestBundle\FOSRestBundle;
use Symfony\Component\HttpFoundation\Request as BaseRequest;

/**
 * Represents a request in FOSRest's zone.
 */
class FOSRestRequest extends BaseRequest
{
    public function initialize(array $query = array(), array $request = array(), array $attributes = array(), array $cookies = array(), array $files = array(), array $server = array(), $content = null)
    {
        parent::initialize($query, $request, $attributes, $cookies, $files, $server, $content);
        $this->attributes->set(FOSRestBundle::ZONE_ATTRIBUTE, true);
    }
}
