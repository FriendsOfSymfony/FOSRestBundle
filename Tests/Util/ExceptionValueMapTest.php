<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests;

use FOS\RestBundle\Util\ExceptionValueMap;
use PHPUnit\Framework\TestCase;

/**
 * ExceptionValueMap test.
 *
 * @author Mikhail Shamin <munk13@gmail.com>
 */
class ExceptionValueMapTest extends TestCase
{
    /**
     * @var ExceptionValueMap
     */
    private $valueMap;

    protected function setUp(): void
    {
        $map = [
            \LogicException::class => 'logic',
            \DomainException::class => 'domain',
            \OutOfBoundsException::class => null,
        ];

        $this->valueMap = new ExceptionValueMap($map);
    }

    public function testResolveExceptionValueIsFound(): void
    {
        $this->assertSame('logic', $this->valueMap->resolveFromClassName(\LogicException::class));
    }

    public function testResolveExceptionValueIsFoundBySubclass(): void
    {
        $this->assertSame('logic', $this->valueMap->resolveFromClassName(\BadFunctionCallException::class));
    }

    public function testResolveExceptionValueNotFound(): void
    {
        $this->assertNull($this->valueMap->resolveFromClassName(\RuntimeException::class));
    }
}
