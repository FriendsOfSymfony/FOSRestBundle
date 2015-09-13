<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Context\Adapter;

/**
 * Defines the interface of adapters.
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
interface SerializerAwareInterface
{
    /**
      * Sets the serializer used.
      *
      * @param mixed            $serializer
      *
      * @return mixed
      */
     public function setSerializer($serializer);
}
