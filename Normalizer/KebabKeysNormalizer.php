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
 * Normalizes the array by changing its keys from kebab-case to camel case.
 *
 * @author Oleg Andreyev <oleg@andreyev.lv>
 */
class KebabKeysNormalizer extends AbstractKeysNormalizer
{
    /**
     * Normalizes a string.
     *
     * @param string $string
     *
     * @return string
     */
    protected function normalizeString(string $string): string
    {
        if (false === strpos($string, '-')) {
            return $string;
        }

        return \preg_replace_callback('/-([a-zA-Z0-9])/', function ($matches) {
            return strtoupper($matches[1]);
        }, $string);
    }
}
