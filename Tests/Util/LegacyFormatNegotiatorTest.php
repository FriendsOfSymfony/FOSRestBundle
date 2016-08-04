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

use FOS\RestBundle\Tests\Negotiation\FormatNegotiatorTest;

/**
 * @deprecated since 1.7, to be removed in 2.0.
 */
class LegacyFormatNegotiatorTest extends FormatNegotiatorTest
{
    const FORMAT_NEGOTIATOR_CLASS = 'FOS\RestBundle\Util\FormatNegotiator';

    public function testInheritance()
    {
        $this->assertInstanceOf('FOS\RestBundle\Util\MediaTypeNegotiatorInterface', $this->negotiator);
        $this->assertInstanceOf('FOS\RestBundle\Util\FormatNegotiatorInterface', $this->negotiator);
    }
}
