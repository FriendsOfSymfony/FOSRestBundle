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

use PHPUnit\Framework\TestCase;

/**
 * QueryParamTest.
 *
 * @author Eduardo Oliveira <entering@gmail.com>
 * @author Ener-Getick <egetick@gmail.com>
 */
class QueryParamTest extends TestCase
{
    public function setUp()
    {
        $this->param = $this->getMockBuilder('FOS\RestBundle\Controller\Annotations\QueryParam')
            ->setMethods(array('getKey'))
            ->getMock();
    }

    public function testInterface()
    {
        $this->assertInstanceOf('FOS\RestBundle\Controller\Annotations\AbstractScalarParam', $this->param);
    }

    public function testValueGetter()
    {
        $this->param
            ->expects($this->once())
            ->method('getKey')
            ->willReturn('foo');

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')->getMock();
        $parameterBag = $this->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')->getMock();
        $parameterBag
            ->expects($this->once())
            ->method('get')
            ->with('foo', 'bar')
            ->willReturn('foobar');
        $request->query = $parameterBag;

        $this->assertEquals('foobar', $this->param->getValue($request, 'bar'));
    }
}
