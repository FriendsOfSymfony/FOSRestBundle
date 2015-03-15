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

use FOS\RestBundle\Controller\Annotations\QueryParam;

/**
 * RequestParamTest
 *
 * @author Eduardo Oliveira <entering@gmail.com>
 */
class QueryParamTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultIsNull()
    {
        $queryParam = new QueryParam();
        $this->assertNull($queryParam->default, 'Expected QueryParam default property to be null');
    }

    public function testStrictIsTrue()
    {
        $queryParam = new QueryParam();
        $this->assertFalse($queryParam->strict, 'Expected QueryParam strict property to be false');
    }

    public function testIncompatiblesIsEmptyArray()
    {
        $queryParam = new QueryParam();
        $this->assertInternalType(
            'array',
            $queryParam->incompatibles,
            'Expected QueryParam incompatibles property to be an array'
        );
        $this->assertEmpty(
            $queryParam->incompatibles,
            'Expected QueryParam incompatibles property to be empty'
        );
    }

}

