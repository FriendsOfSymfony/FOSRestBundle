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
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;

/**
 * FormatNegotiatorTest.
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
class FormatNegotiatorTest extends TestCase
{
    private $requestStack;
    private $request;
    private $negotiator;

    public function setUp()
    {
        $this->requestStack = new RequestStack();
        $this->request = new Request();
        $this->requestStack->push($this->request);
        $this->negotiator = new FormatNegotiator($this->requestStack, ['json' => ['application/json;version=1.0']]);
    }

    public function testEmptyRequestMatcherMap()
    {
        $this->assertNull($this->negotiator->getBest(''));
    }

    /**
     * @expectedException \FOS\RestBundle\Util\StopFormatListenerException
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

    public function testFallbackFormatWithPriorities()
    {
        $this->addRequestMatcher(true, ['priorities' => ['json', 'xml'], 'fallback_format' => null]);
        $this->assertNull($this->negotiator->getBest(''));

        $this->addRequestMatcher(true, ['priorities' => ['json', 'xml'], 'fallback_format' => 'json']);
        $this->assertEquals(new Accept('application/json'), $this->negotiator->getBest(''));
    }

    public function testGetBest()
    {
        $this->request->headers->set('Accept', 'application/xhtml+xml, text/html, application/xml;q=0.9, */*;q=0.8');
        $priorities = ['text/html; charset=UTF-8', 'html', 'application/json'];
        $this->addRequestMatcher(true, ['priorities' => $priorities]);

        $this->assertEquals(
            new Accept('text/html;charset=utf-8'),
            $this->negotiator->getBest('')
        );

        $this->request->headers->set('Accept', 'application/xhtml+xml, application/xml;q=0.9, */*;q=0.8');
        $this->assertEquals(
            new Accept('application/xhtml+xml'),
            $this->negotiator->getBest('', ['html', 'json'])
        );
    }

    public function testGetBestFallback()
    {
        $this->request->headers->set('Accept', 'text/html');
        $priorities = ['application/json'];
        $this->addRequestMatcher(true, ['priorities' => $priorities, 'fallback_format' => 'xml']);
        $this->assertEquals(new Accept('text/xml'), $this->negotiator->getBest(''));
    }

    public function testGetBestWithFormat()
    {
        $this->request->headers->set('Accept', 'application/json;version=1.0');
        $priorities = ['json'];
        $this->addRequestMatcher(true, ['priorities' => $priorities, 'fallback_format' => 'xml']);
        $this->assertEquals(new Accept('application/json;version=1.0'), $this->negotiator->getBest(''));
    }

    public function testGetBestWithPreferExtension()
    {
        $priorities = ['text/html', 'application/json'];
        $this->addRequestMatcher(true, ['priorities' => $priorities, 'prefer_extension' => '2.0']);

        $reflectionClass = new \ReflectionClass(get_class($this->request));
        $reflectionProperty = $reflectionClass->getProperty('pathInfo');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->request, '/file.json');

        // Without extension mime-type in Accept header

        $this->request->headers->set('Accept', 'text/html; q=1.0');
        $this->assertEquals(new Accept('application/json'), $this->negotiator->getBest(''));

        // With low q extension mime-type in Accept header

        $this->request->headers->set('Accept', 'text/html; q=1.0, application/json; q=0.1');
        $this->assertEquals(new Accept('application/json'), $this->negotiator->getBest(''));

        $reflectionProperty->setValue($this->request, null);
    }

    public function testGetBestWithPreferExtensionAndUnknownExtension()
    {
        $priorities = ['text/html', 'application/json'];
        $this->addRequestMatcher(true, ['priorities' => $priorities, 'prefer_extension' => '2.0']);

        $reflectionClass = new \ReflectionClass(get_class($this->request));
        $reflectionProperty = $reflectionClass->getProperty('pathInfo');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->request, '/file.123456789');

        $this->request->headers->set('Accept', 'text/html, application/json');
        $this->assertEquals(new Accept('text/html'), $this->negotiator->getBest(''));

        $reflectionProperty->setValue($this->request, null);
    }

    public function testGetBestWithFormatWithRequestMimeTypeFallback()
    {
        $negotiator = new FormatNegotiator($this->requestStack);

        $this->request->headers->set('Accept', 'application/json');
        $priorities = ['json'];
        $this->addRequestMatcher(true, ['priorities' => $priorities, 'fallback_format' => 'xml']);
        $this->assertEquals(new Accept('application/json'), $this->negotiator->getBest(''));
    }

    /**
     * @param bool  $match
     * @param array $options
     */
    private function addRequestMatcher($match, array $options = [])
    {
        $matcher = $this->getMockBuilder('Symfony\Component\HttpFoundation\RequestMatcherInterface')->getMock();

        $matcher->expects($this->any())
            ->method('matches')
            ->with($this->request)
            ->willReturn($match);

        $this->negotiator->add($matcher, $options);
    }
}
