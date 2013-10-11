<?php

/*
 * This file is part of the FOSRest package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Decoder;

/**
 * Defines the interface of decoder providers
 *
 * @author Igor Wiedler <igor@wiedler.ch>
 */
interface DecoderProviderInterface
{
    /**
     * Check if a certain format is supported.
     *
     * @param string $format Format for the requested decoder.
     * @return Boolean
     */
    function supports($format);

    /**
     * Provides decoders, possibly lazily.
     *
     * @param string $format Format for the requested decoder.
     * @return FOS\RestBundle\Decoder\DecoderInterface
     */
    function getDecoder($format);
}
