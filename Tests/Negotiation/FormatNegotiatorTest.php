<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Negotiatior;

use FOS\RestBundle\Negotiation\FormatNegotiator;
use Negotiation\Accept;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;

/**
 * FormatNegotiatorTest.
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
class FormatNegotiatorTest extends \PHPUnit_Framework_TestCase
{
    private $requestStack;
    private $request;
    private $negotiator;

    public function setUp()
    {
        $this->requestStack = new RequestStack();
        $this->request = new Request();
        $this->requestStack->push($this->request);
        $this->negotiator = new FormatNegotiator($this->requestStack);
    }

    public function testEmptyRequestMatcherMap()
    {
        $this->assertNull($this->negotiator->getBest(''));
    }

    /**
     * @expectedException FOS\RestBundle\Util\StopFormatListenerException
     * @expectedExceptionMessage Stopped
     */
    public function testStopException()
    {
        $this->addRequestMatcher(false);
        $this->addRequestMatcher(true, ['stop' => true]);
        $this->negotiator->getBest('');
    }

    public function testFallbackFormat()
    {
        $this->addRequestMatcher(true, ['fallback_format' => null]);
        $this->assertNull($this->negotiator->getBest(''));

        $this->addRequestMatcher(true, ['fallback_format' => 'html']);
        $this->assertEquals(new Accept('text/html'), $this->negotiator->getBest(''));
    }

    public function testGetBest()
    {
        $this->request->headers->set('Accept', 'text/html, application/xhtml+xml, application/xml;q=0.9, */*;q=0.8');
        $priorities = ['text/html; charset=UTF-8', 'application/json'];
        $this->addRequestMatcher(true, ['priorities' => $priorities]);

        $this->assertEquals(new Accept('text/html; charset=UTF-8'), $this->negotiator->getBest(''));
    }

    public function testGetBestFallback()
    {
        $this->request->headers->set('Accept', 'text/html');
        $priorities = ['application/json'];
        $this->addRequestMatcher(true, ['priorities' => $priorities, 'fallback_format' => 'xml']);
        $this->assertEquals(new Accept('text/xml'), $this->negotiator->getBest(''));
    }

    /**
     * @param bool  $match
     * @param array $options
     */
    private function addRequestMatcher($match, array $options = [])
    {
        $matcher = $this->getMock('Symfony\Component\HttpFoundation\RequestMatcherInterface');

        $matcher->expects($this->any())
            ->method('matches')
            ->with($this->request)
            ->willReturn($match);

        $this->negotiator->add($matcher, $options);
    }
}
