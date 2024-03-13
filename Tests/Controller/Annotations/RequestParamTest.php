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
use FOS\RestBundle\Controller\Annotations\RequestParam;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * RequestParamTest.
 *
 * @author Eduardo Oliveira <entering@gmail.com>
 * @author Ener-Getick <egetick@gmail.com>
 */
class RequestParamTest extends TestCase
{
    protected function setUp(): void
    {
        $this->param = $this->getMockBuilder(RequestParam::class)
            ->setMethods(['getKey'])
            ->getMock();
    }

    public function testInterface(): void
    {
        $this->assertInstanceOf(AbstractScalarParam::class, $this->param);
    }

    public function testValueGetter(): void
    {
        $this->param
            ->expects($this->once())
            ->method('getKey')
            ->willReturn('foo');

        $request = $this->getMockBuilder(Request::class)->getMock();

        if (class_exists(InputBag::class)) {
            $bag = new InputBag();
        } else {
            $bag = new ParameterBag();
        }

        $bag->set('foo', 'foobar');
        $request->request = $bag;

        $this->assertEquals('foobar', $this->param->getValue($request, 'bar'));
    }
}
