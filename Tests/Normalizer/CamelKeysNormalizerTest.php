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
        $normalizer->normalize([
            'foo' => [
                'foo_bar' => 'foo',
                'foo_Bar' => 'foo',
            ],
        ]);
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
        return [
            [[], []],
            [
                ['foo' => ['Foo_bar_baz' => ['foo_Bar' => ['foo_bar' => 'foo_bar']]],
                    'foo_1ar' => ['foo_bar'],
                ],
                ['foo' => ['FooBarBaz' => ['fooBar' => ['fooBar' => 'foo_bar']]],
                    'foo1ar' => ['foo_bar'],
                ],
            ],
        ];
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
