<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Decoder;

use FOS\RestBundle\Decoder\JsonToFormDecoder;
use PHPUnit\Framework\TestCase;

/**
 * Tests the form-like encoder.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class JsonToFormDecoderTest extends TestCase
{
    public function testDecodeWithRemovingFalseData()
    {
        $data = [
            'arrayKey' => [
                'falseKey' => false,
                'stringKey' => 'foo',
            ],
            'falseKey' => false,
            'trueKey' => true,
            'intKey' => 69,
            'floatKey' => 3.14,
            'stringKey' => 'bar',
        ];
        $decoder = new JsonToFormDecoder();
        $decoded = $decoder->decode(json_encode($data));

        $this->assertInternalType('array', $decoded);
        $this->assertInternalType('array', $decoded['arrayKey']);
        $this->assertNull($decoded['arrayKey']['falseKey']);
        $this->assertEquals('foo', $decoded['arrayKey']['stringKey']);
        $this->assertNull($decoded['falseKey']);
        $this->assertEquals('1', $decoded['trueKey']);
        $this->assertEquals('69', $decoded['intKey']);
        $this->assertEquals('3.14', $decoded['floatKey']);
        $this->assertEquals('bar', $decoded['stringKey']);
    }
}
