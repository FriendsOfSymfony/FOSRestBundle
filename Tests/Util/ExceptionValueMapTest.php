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

/**
 * ExceptionValueMap test.
 *
 * @author Mikhail Shamin <munk13@gmail.com>
 */
class ExceptionValueMapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ExceptionValueMap
     */
    private $valueMap;

    protected function setUp()
    {
        $map = [
            \LogicException::class => 'logic',
            \DomainException::class => 'domain',
            \OutOfBoundsException::class => null,
        ];

        $this->valueMap = new ExceptionValueMap($map);
    }

    public function testResolveExceptionValueIsFound()
    {
        $this->assertSame('logic', $this->valueMap->resolveException(new \LogicException()));
    }

    public function testResolveExceptionValueIsFoundBySubclass()
    {
        $this->assertSame('logic', $this->valueMap->resolveException(new \BadFunctionCallException()));
    }

    public function testResolveExceptionValueNotFound()
    {
        $this->assertFalse($this->valueMap->resolveException(new \RuntimeException()));
    }

    public function testResolveExceptionNullValueIsSkipped()
    {
        $this->assertFalse($this->valueMap->resolveClass(new \OutOfBoundsException()));
    }

    public function testResolveClassValueIsFound()
    {
        $this->assertSame('logic', $this->valueMap->resolveClass(\LogicException::class));
    }

    public function testResolveClassValueIsFoundBySubclass()
    {
        $this->assertSame('logic', $this->valueMap->resolveClass(\BadFunctionCallException::class));
    }

    public function testResolveClassValueNotFound()
    {
        $this->assertFalse($this->valueMap->resolveClass(\RuntimeException::class));
    }

    public function testResolveClassNullValueIsSkipped()
    {
        $this->assertFalse($this->valueMap->resolveClass(\OutOfBoundsException::class));
    }
}
