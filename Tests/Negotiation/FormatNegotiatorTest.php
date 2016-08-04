<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Negotiation;

class FormatNegotiatorTest extends \PHPUnit_Framework_TestCase
{
    const FORMAT_NEGOTIATOR_CLASS = 'FOS\RestBundle\Negotiation\FormatNegotiator';

    protected $negotiator;

    protected function setUp()
    {
        $className = static::FORMAT_NEGOTIATOR_CLASS;
        $this->negotiator = new $className();
    }

    public function testLegacyInheritance()
    {
        $this->assertInstanceOf('FOS\RestBundle\Util\FormatNegotiator', $this->negotiator);
    }
}
