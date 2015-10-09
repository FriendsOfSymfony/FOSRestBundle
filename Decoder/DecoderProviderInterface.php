<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Decoder;

/**
 * Defines the interface of decoder providers.
 *
 * @author Igor Wiedler <igor@wiedler.ch>
 */
interface DecoderProviderInterface
{
    /**
     * Checks if a certain format is supported.
     *
     * @param string $format
     *
     * @return bool
     */
    public function supports($format);

    /**
     * Provides decoders, possibly lazily.
     *
     * @param string $format
     *
     * @return \FOS\RestBundle\Decoder\DecoderInterface
     */
    public function getDecoder($format);
}
