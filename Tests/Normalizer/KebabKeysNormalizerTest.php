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

use FOS\RestBundle\Normalizer\KebabKeysNormalizer;
use PHPUnit\Framework\TestCase;

class KebabKeysNormalizerTest extends TestCase
{
    /**
     * @expectedException \FOS\RestBundle\Normalizer\Exception\NormalizationException
     */
    public function testNormalizeSameValueException()
    {
        $normalizer = new KebabKeysNormalizer();
        $normalizer->normalize([
            'foo' => [
                'foo-bar' => 'foo',
                'foo-Bar' => 'foo',
            ],
        ]);
    }

    /**
     * @dataProvider normalizeProvider
     */
    public function testNormalize(array $array, array $expected)
    {
        $normalizer = new KebabKeysNormalizer();
        $this->assertEquals($expected, $normalizer->normalize($array));
    }

    public function normalizeProvider()
    {
        return array(
            array(array(), array()),
            array(
                array('foo' => array('Foo-bar-baz' => array('foo-Bar' => array('foo-bar' => 'foo_bar'))),
                      'foo-1ar' => array('foo_bar'),
                ),
                array('foo' => array('FooBarBaz' => array('fooBar' => array('fooBar' => 'foo_bar'))),
                      'foo1ar' => array('foo_bar'),
                ),
            ),
            array(
                array('_-username' => 'foo', '-password' => 'bar', '-foo-bar' => 'foobar'),
                array('_Username' => 'foo', 'Password' => 'bar', 'FooBar' => 'foobar')
            ),
        );
    }
}
