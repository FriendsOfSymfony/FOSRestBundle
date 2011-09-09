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
     * @dataProvider getWords
     *
     * @param string $singular
     * @param string $plural
     */
    public function testPluralize($singular, $plural)
    {
        $this->assertEquals($plural, Pluralization::pluralize($singular));
    }

    /**
     * Test that pluralization singularize words correctly.
     *
     * @dataProvider getWords
     *
     * @param string $singular
     * @param string $plural
     */
    public function testSingularize($singular, $plural)
    {
        $this->assertEquals($singular, Pluralization::singularize($plural));
    }

    /**
     * Words provider. 
     * 
     * @return  array of hashes (singular => plural)
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
}
