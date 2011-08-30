<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Util;

/**
 * Pluralization object.
 *
 * @author Bulat Shakirzyanov <avalanche123>
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class Pluralization
{
    /**
     * Pluralizes English noun.
     *
     * @param  string  $word english noun to pluralize
     *
     * @return string        plural noun
     */
    public static function pluralize($word)
    {
        $plurals = array(
            '/(quiz)$/i'                => '\1zes',
            '/^(ox)$/i'                 => '\1en',
            '/([m|l])ouse$/i'           => '\1ice',
            '/(matr|vert|ind)ix|ex$/i'  => '\1ices',
            '/(x|ch|ss|sh)$/i'          => '\1es',
            '/([^aeiouy]|qu)ies$/i'     => '\1y',
            '/([^aeiouy]|qu)y$/i'       => '\1ies',
            '/(hive)$/i'                => '\1s',
            '/(?:([^f])fe|([lr])f)$/i'  => '\1\2ves',
            '/sis$/i'                   => 'ses',
            '/([ti])um$/i'              => '\1a',
            '/(buffal|tomat)o$/i'       => '\1oes',
            '/(bu)s$/i'                 => '\1ses',
            '/(alias|status)/i'         => '\1es',
            '/(octop|vir)us$/i'         => '\1i',
            '/(ax|test)is$/i'           => '\1es',
            '/s$/i'                     => 's',
            '/$/'                       => 's'
        );
        $uncountables = array(
            'equipment', 'information', 'rice', 'money', 'species', 'series', 'fish', 'sheep'
        );
        $irregulars = array(
            'person'  => 'people',
            'man'     => 'men',
            'child'   => 'children',
            'sex'     => 'sexes',
            'move'    => 'moves'
        );
        $lowerCasedWord = strtolower($word);
        foreach ($uncountables as $uncountable) {
            if (substr($lowerCasedWord, (-1 * strlen($uncountable))) == $uncountable) {
                return $word;
            }
        }
        foreach ($irregulars as $plural => $singular) {
            if (preg_match('/(' . $plural . ')$/i', $word, $arr)) {
                return preg_replace(
                    '/(' . $plural . ')$/i',
                    substr($arr[0], 0, 1) . substr($singular, 1),
                    $word
                );
            }
        }
        foreach ($plurals as $rule => $replacement) {
            if (preg_match($rule, $word)) {
                return preg_replace($rule, $replacement, $word);
            }
        }

        return false;
    }

    /**
     * Singularizes English plural.
     *
     * @param  string  $word English noun to singularize
     *
     * @return string        Singular noun.
     */
    public static function singularize($word)
    {
        $singulars = array(
            '/(quiz)zes$/i'         => '\1',
            '/(matr)ices$/i'        => '\1ix',
            '/(vert|ind)ices$/i'    => '\1ex',
            '/^(ox)en/i'            => '\1',
            '/(alias|status)es$/i'  => '\1',
            '/([octop|vir])i$/i'    => '\1us',
            '/(cris|ax|test)es$/i'  => '\1is',
            '/(shoe)s$/i'           => '\1',
            '/(o)es$/i'             => '\1',
            '/(bus)es$/i'           => '\1',
            '/([m|l])ice$/i'        => '\1ouse',
            '/(x|ch|ss|sh)es$/i'    => '\1',
            '/(m)ovies$/i'          => '\1ovie',
            '/(s)eries$/i'          => '\1eries',
            '/([^aeiouy]|qu)ies$/i' => '\1y',
            '/([lr])ves$/i'         => '\1f',
            '/(tive)s$/i'           => '\1',
            '/(hive)s$/i'           => '\1',
            '/([^f])ves$/i'         => '\1fe',
            '/(^analy)ses$/i'       => '\1sis',
            '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\1\2sis',
            '/([ti])a$/i'           => '\1um',
            '/(n)ews$/i'            => '\1ews',
            '/s$/i'                 => '',
        );
        $uncountables = array(
            'equipment', 'information', 'rice', 'money', 'species', 'series', 'fish', 'sheep'
        );
        $irregulars = array(
            'person'  => 'people',
            'man'     => 'men',
            'child'   => 'children',
            'sex'     => 'sexes',
            'move'    => 'moves'
        );
        $lowerCasedWord = strtolower($word);
        foreach ($uncountables as $uncountable) {
            if (substr($lowerCasedWord, (-1 * strlen($uncountable))) == $uncountable) {
                return $word;
            }
        }
        foreach ($irregulars as $plural => $singular) {
            if (preg_match('/(' . $singular.')$/i', $word, $arr)) {
                return preg_replace(
                    '/(' . $singular . ')$/i',
                    substr($arr[0], 0, 1) . substr($plural, 1),
                    $word
                );
            }
        }
        foreach ($singulars as $rule => $replacement) {
            if (preg_match($rule, $word)) {
                return preg_replace($rule, $replacement, $word);
            }
        }

        return $word;
    }
}
