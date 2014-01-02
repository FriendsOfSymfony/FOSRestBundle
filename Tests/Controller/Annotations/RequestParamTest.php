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

use FOS\RestBundle\Controller\Annotations\RequestParam;

/**
 * RequestParamTest
 *
 * @author Eduardo Oliveira <entering@gmail.com>
 */
class RequestParamTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultIsNull()
    {
        $requestParam = new RequestParam();
        $this->assertNull($requestParam->default, 'Expected RequestParam default property to be null');
    }

    public function testStrictIsTrue()
    {
        $requestParam = new RequestParam();
        $this->assertTrue($requestParam->strict, 'Expected RequestParam strict property to be true');
    }
}
