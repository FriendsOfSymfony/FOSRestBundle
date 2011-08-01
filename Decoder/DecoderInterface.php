<?php

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
     * @return mixed
     */
    function decode($data);
}
