<?php

namespace FOS\RestBundle\Tests\Version\Resolver;

use FOS\RestBundle\Version\Resolver\PathVersionResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class PathVersionResolverTest extends TestCase
{
    /**
     * @covers \FOS\RestBundle\Version\Resolver\PathVersionResolver
     */
    public function testResolve()
    {
        $resolver = new PathVersionResolver('/^\\/api\\/(?P<version>v?[0-9\.]+)\\//');

        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/api/v2/some/action']);
        $version = $resolver->resolve($request);

        $this->assertEquals('v2', $version);

        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/api/v2.7/some/action']);
        $version = $resolver->resolve($request);

        $this->assertEquals('v2.7', $version);

        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/api/1/some/action']);
        $version = $resolver->resolve($request);

        $this->assertEquals('1', $version);

        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/api/1.1.1/some/action']);
        $version = $resolver->resolve($request);

        $this->assertEquals('1.1.1', $version);

        $request = new Request([], [], [], [], [], ['REQUEST_URI' => '/api/some/action']);
        $version = $resolver->resolve($request);

        $this->assertEquals(false, $version);
    }
}
