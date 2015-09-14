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

use FOS\RestBundle\Context\ContextInterface;

/**
 * Converts a deserialization context.
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
interface DeserializationContextAdapterInterface
{
    /**
      * Converts a deserialization context.
      *
      * @param ContextInterface $context
      *
      * @throws \LogicException if the specialization is not supported
      *
      * @return mixed
      */
     public function convertDeserializationContext(ContextInterface $context);

      /**
       * Checks if supports a deserialization context.
       *
       * @param ContextInterface $context
       *
       * @return mixed
       */
      public function supportsDeserialization(ContextInterface $context);
}
