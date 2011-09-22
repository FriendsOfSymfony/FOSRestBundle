<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\DecoderProvider;

/**
 * Defines the interface of decoder providers
 *
 * @author Igor Wiedler <igor@wiedler.ch>
 */
interface DecoderProviderInterface
{
    /**
     * Provides decoders, possibly lazily.
     *
     * @param string $id Identifier of the requested decoder.
     * @return mixed
     */
    function getDecoder($id);
}
