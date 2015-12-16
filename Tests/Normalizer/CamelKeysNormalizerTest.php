<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Normalizer;

use FOS\RestBundle\Normalizer\CamelKeysNormalizer;
use FOS\RestBundle\Normalizer\CamelKeysNormalizerWithLeadingUnderscore;

class CamelKeysNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \FOS\RestBundle\Normalizer\Exception\NormalizationException
     */
    public function testNormalizeSameValueException()
    {
        $normalizer = new CamelKeysNormalizer();
        $normalizer->normalize(array(
            'foo' => array(
                'foo_bar' => 'foo',
                'foo_Bar' => 'foo',
            ),
        ));
    }

    /**
     * @dataProvider normalizeProvider
     */
    public function testNormalize(array $array, array $expected)
    {
        $normalizer = new CamelKeysNormalizer();
        $this->assertEquals($expected, $normalizer->normalize($array));
    }

    public function normalizeProvider()
    {
        return array(
            array(array(), array()),
            array(
                array('foo' => array('Foo_bar_baz' => array('foo_Bar' => array('foo_bar' => 'foo_bar'))),
                    'foo_1ar' => array('foo_bar'),
                ),
                array('foo' => array('FooBarBaz' => array('fooBar' => array('fooBar' => 'foo_bar'))),
                    'foo1ar' => array('foo_bar'),
                ),
            ),
        );
    }

    /**
     * @dataProvider normalizeProvider
     */
    public function testNormalizeLeadingUnderscore(array $array, array $expected)
    {
        $normalizer = new CamelKeysNormalizerWithLeadingUnderscore();
        $this->assertEquals($expected, $normalizer->normalize($array));
    }

    public function normalizeProviderLeadingUnderscore()
    {
        $array = $this->normalizeProvider();
        $array[] = array(array('__username' => 'foo', '_password' => 'bar', '_foo_bar' => 'foobar'), array('__username' => 'foo', '_password' => 'bar', '_fooBar' => 'foobar'));

        return $array;
    }
}
