<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Util;

use FOS\RestBundle\Util\FormatNegotiator;

use Symfony\Component\HttpFoundation\Request;

class FormatNegotiatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getData
     */
    public function testGetBestFormat($acceptHeader, $format, $priorities, $preferExtension, $expected)
    {
        $request = new Request();
        $request->headers->set('Accept', $acceptHeader);
        $request->attributes->set('_format', $format);

        $formatNegotiator = new FormatNegotiator();

        $this->assertEquals($expected, $formatNegotiator->getBestFormat($request, $priorities, $preferExtension));
    }

    public function getData()
    {
        return array(
            array(null, null, array('html', 'json', '*/*'), false, null),
            array('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', null, array('html', 'json', '*/*'), false, 'html'),
            array('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', 'json', array('html', 'json', '*/*'), false, 'html'),
            array('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', 'json', array('html', 'json', '*/*'), true, 'json'),
            array('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', 'json', array('rss', '*/*'), false, 'html'),
            array('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', 'json', array('xml'), false, 'xml'),
            array('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', 'json', array('json', 'xml'), false, 'xml'),
            array('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', 'json', array('json'), false, 'json'),
            array('text/html,application/xhtml+xml,application/xml;q=0.9,*/*', 'json', array('json'), false, 'json'),
            array('text/html,application/xhtml+xml,application/xml;q=0.9,*/*', null, array('json'), false, 'json'),
            array('text/html,application/xhtml+xml,application/xml', null, array('json'), false, null),
        );
    }}
