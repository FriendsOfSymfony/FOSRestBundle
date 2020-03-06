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
     * @return bool
     */
    public function supports(string $format);

    /**
     * Provides decoders, possibly lazily.
     *
     * @return DecoderInterface
     */
    public function getDecoder(string $format);
}
