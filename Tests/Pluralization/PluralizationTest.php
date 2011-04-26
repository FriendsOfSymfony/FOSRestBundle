<?php

namespace FOS\RestBundle\Tests\Pluralization;

use FOS\RestBundle\Pluralization\Pluralization;

/*
 * This file is part of the FOSRestBundle
 *
 * (c) Lukas Kahwe Smith <smith@pooteeweet.org>
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 * (c) Bulat Shakirzyanov <mallluhuct@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

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
