<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Normalizer;

/**
 * Normalizes the array by changing its keys from underscore to camel case, while
 * leaving leading underscores unchanged.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class CamelKeysNormalizerWithLeadingUnderscore extends CamelKeysNormalizer
{
    /**
     * Normalizes a string while leaving leading underscores unchanged.
     *
     * @param string $string
     *
     * @return string
     */
    protected function normalizeString($string)
    {
        if (false === strpos($string, '_')) {
            return $string;
        }

        $offset = strspn($string, '_');
        if ($offset) {
            $underscorePrefix = substr($string, 0, $offset);
            $string = substr($string, $offset);
        } else {
            $underscorePrefix = '';
        }

        return $underscorePrefix.parent::normalizeString($string);
    }
}
