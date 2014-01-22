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

use FOS\RestBundle\Decoder\DecoderInterface;

/**
 * Decodes JSON data and make it compliant with application/x-www-form-encoded style
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class JsonToFormDecoder implements DecoderInterface
{

    /**
     * Makes data decoded from JSON application/x-www-form-encoded compliant 
     * 
     * @param array $data
     */
    private function xWwwFormEncodedLike(&$data)
    {
        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                // Encode recursively
                $this->xWwwFormEncodedLike($value);
            } elseif (false === $value) {
                // Checkbox-like behavior: remove false data
                unset($data[$key]);
            } elseif (!is_string($value)) {
                // Convert everything to string
                // true values will be converted to '1', this is the default checkbox behavior
                $value = strval($value);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function decode($data)
    {
        $decodedData = @json_decode($data, true);
        if ($decodedData) {
            $this->xWwwFormEncodedLike($decodedData);
        }

        return $decodedData;
    }

}