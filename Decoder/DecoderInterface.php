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
 * Defines the interface of decoders
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
interface DecoderInterface
{
    /**
     * Decodes a string into PHP data
     *
     * @param string $data data to decode
     * @return array|Boolean false in case the content could not be decoded, else an array
     */
    function decode($data);
}
