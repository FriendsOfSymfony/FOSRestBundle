<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Form\Type;

use FOS\RestBundle\Form\Type\BooleanType;
use Symfony\Component\Form\Test\TypeTestCase;

class BooleanTypeTest extends TypeTestCase
{
    /**
     * @dataProvider getTestData
     *
     * @param mixed $value
     * @param bool  $expected
     */
    public function testFormType($value, $expected)
    {
        // SF >= 2.8
        if (method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')) {
            $form = $this->factory->create('FOS\RestBundle\Form\Type\BooleanType');
        } else {
            $form = $this->factory->create(new BooleanType());
        }

        $form->submit($value);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expected, $form->getData());
    }

    public function getTestData()
    {
        return array(
            array('1', true),
            array(1, true),
            array(true, true),
            array('0', false),
            array(0, false),
            array(false, false),
            array('yes', false),
            array('no', false),
        );
    }
}
