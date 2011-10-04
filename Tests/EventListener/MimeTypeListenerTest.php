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

use Symfony\Component\HttpFoundation\Request,
    FOS\RestBundle\EventListener\MimeTypeListener;

/**
 * Request listener test
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class MimeTypeListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testOnKernelRequest()
    {
        $listener = new MimeTypeListener(array('jsonp' => 'application/javascript'));

        $request = new Request;
        $event = $this->getMockBuilder('\Symfony\Component\HttpKernel\Event\GetResponseEvent')->disableOriginalConstructor()->getMock();
        $event->expects($this->once())
              ->method('getRequest')
              ->will($this->returnValue($request));

        $this->assertNull($request->getMimeType('jsonp'));

        $listener->onKernelRequest($event);

        $this->assertEquals('application/javascript', $request->getMimeType('jsonp'));
    }
}
