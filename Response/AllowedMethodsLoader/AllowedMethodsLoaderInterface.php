<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Response\AllowedMethodsLoader;

/**
 * AllowedMethodsLoaderInterface.
 *
 * @author Boris Guéry <guery.b@gmail.com>
 */
interface AllowedMethodsLoaderInterface
{
    /**
     * Returns the allowed http methods.
     *
     * array(
     *  'some_route'    => array('GET', 'POST'),
     *  'another_route' => array('DELETE', 'PUT'),
     * );
     *
     * @return array
     */
    public function getAllowedMethods();
}
