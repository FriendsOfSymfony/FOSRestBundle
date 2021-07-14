<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Controller\Annotations;

use FOS\RestBundle\Controller\Annotations\AbstractScalarParam;
use FOS\RestBundle\Controller\Annotations\HeaderParam;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * HeaderParamTest.
 *
 * @author Ilia Shcheglov <ilia.sheglov@gmail.com>
 */
final class HeaderParamTest extends TestCase
{
    protected function setUp(): void
    {
        $this->param = $this->getMockBuilder(HeaderParam::class)
            ->setMethods(['getKey'])
            ->getMock();
    }

    public function testInterface()
    {
        self::assertInstanceOf(AbstractScalarParam::class, $this->param);
    }

    public function testValueGetter()
    {
        $this->param
            ->expects(self::once())
            ->method('getKey')
            ->willReturn('foo');

        $request = $this->createMock(Request::class);
        $headerBag = new HeaderBag();
        $headerBag->set('foo', 'foobar');
        $request->headers = $headerBag;

        self::assertEquals('foobar', $this->param->getValue($request, 'bar'));
    }
}
