<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Fixtures\Context\Adapter;

use FOS\RestBundle\Context\Adapter\DeserializationContextAdapterInterface;
use FOS\RestBundle\Context\Adapter\SerializationContextAdapterInterface;
use FOS\RestBundle\Context\Adapter\SerializerAwareInterface;

/**
 * {@inheritdoc}
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
interface SerializerAwareAdapter extends SerializationContextAdapterInterface, DeserializationContextAdapterInterface, SerializerAwareInterface
{
}
