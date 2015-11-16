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

/**
 * ParamTest.
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
class ParamTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->param = $this->getMockForAbstractClass('FOS\RestBundle\Controller\Annotations\Param');
    }

    public function testInterface()
    {
        $this->assertInstanceOf('FOS\RestBundle\Controller\Annotations\AbstractScalarParam', $this->param);
    }
}
