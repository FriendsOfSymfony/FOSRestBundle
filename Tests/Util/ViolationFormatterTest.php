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

use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Util\ViolationFormatter;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * ViolationFormatter test.
 *
 * @author Loick Piera <pyrech@gmail.com>
 */
class ViolationFormatterTest extends \PHPUnit_Framework_TestCase
{
    public function testViolationIsWellFormatted()
    {
        $violation = $this->getMockBuilder('Symfony\Component\Validator\ConstraintViolation')
            ->disableOriginalConstructor()
            ->getMock();

        $violation->expects($this->once())
            ->method('getInvalidValue')
            ->will($this->returnValue('bar'));

        $violation->expects($this->once())
            ->method('getMessage')
            ->will($this->returnValue('expected message'));

        $param = new QueryParam();
        $param->name = 'foo';

        $formatter = new ViolationFormatter();
        $this->assertEquals(
            "Query parameter foo value 'bar' violated a constraint (expected message)",
            $formatter->format($param, $violation)
        );
    }

    public function testViolationListIsWellFormatted()
    {
        $errors = new ConstraintViolationList(array(
            new ConstraintViolation('expected message 1', null, array(), null, null, 'bar'),
            new ConstraintViolation('expected message 2', null, array(), null, null, 'bar'),
        ));

        $param = new RequestParam();
        $param->name = 'foo';

        $formatter = new ViolationFormatter();
        $this->assertEquals(
            "Request parameter foo value 'bar' violated a constraint (expected message 1)"
            ."\nRequest parameter foo value 'bar' violated a constraint (expected message 2)",
            $formatter->formatList($param, $errors)
        );
    }
}
