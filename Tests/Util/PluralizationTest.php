<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Util;

use FOS\RestBundle\Util\Pluralization;

/**
 * Pluralization test.
 *
 * @author Bulat Shakirzyanov <avalanche123>
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class PluralizationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test that pluralization pluralize words correctly.
     *
     * @dataProvider getPluralizableWords
     *
     * @param string $source
     * @param string $plural
     */
    public function testPluralize($source, $plural)
    {
        $this->assertEquals($plural, Pluralization::pluralize($source));
    }

    /**
     * Test that pluralization singularize words correctly.
     *
     * @dataProvider getSingularizableWords
     *
     * @param string $singular
     * @param string $source
     */
    public function testSingularize($singular, $source)
    {
        $this->assertEquals($singular, Pluralization::singularize($source));
    }

    /**
     * Words provider.
     *
     * @return array of hashes (singular => plural)
     */
    public function getWords()
    {
        return array(
            array('company',    'companies'),
            array('user',       'users'),
            array('person',     'people'),
            array('news',       'news'),
            array('comment',    'comments'),
        );
    }

    /**
     * Singularizable words provider.
     *
     * @return array of hashes (singular => source)
     */
    public function getSingularizableWords()
    {
        $singularizables = array(
            array('company',    'company'),
        );

        $words = $this->getWords();

        return array_merge($words, $singularizables);
    }

    /**
     * Pluralizable words provider.
     *
     * @return array of hashes (source => plural)
     */
    public function getPluralizableWords()
    {
        $pluralizables = array(
            array('companies',    'companies'),
        );

        $words = $this->getWords();

        return array_merge($words, $pluralizables);
    }
}
