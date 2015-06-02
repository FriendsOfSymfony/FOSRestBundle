<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use FOS\RestBundle\EventListener\MimeTypeListener;

/**
 * Request listener test
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class MimeTypeListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testOnKernelRequest()
    {
        $formatNegotiator = $this->getMockBuilder('FOS\RestBundle\Util\FormatNegotiator')
            ->disableOriginalConstructor()->getMock();
        $formatNegotiator->expects($this->any())
            ->method('registerFormat')
            ->with('jsonp', array('application/javascript+jsonp'), true)
            ->will($this->returnValue(null));

        $listener = new MimeTypeListener(array('enabled' => true, 'formats' => array('jsonp' => array('application/javascript+jsonp'))), $formatNegotiator);

        $request = new Request();
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->any())
              ->method('getRequest')
              ->will($this->returnValue($request));

        $this->assertNull($request->getMimeType('jsonp'));

        $listener->onKernelRequest($event);

        $this->assertNull($request->getMimeType('jsonp'));

        $event->expects($this->once())
              ->method('getRequestType')
              ->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));

        $listener->onKernelRequest($event);

        $this->assertEquals('application/javascript+jsonp', $request->getMimeType('jsonp'));
    }
}
