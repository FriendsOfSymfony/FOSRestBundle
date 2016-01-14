<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Form\Extension;

use FOS\RestBundle\Form\Extension\BooleanExtension;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;

/**
 * @author GeLo <geloen.eric@gmail.com>
 */
class BooleanExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->formFactory = Forms::createFormFactoryBuilder()
            ->addTypeExtension(new BooleanExtension())
            ->getFormFactory();
    }

    public function testCheckboxType()
    {
        $viewTransformers = $this->createCheckboxForm()->getConfig()->getViewTransformers();

        $this->assertCount(1, $viewTransformers);
        $this->assertInstanceOf(
            'Symfony\Component\Form\Extension\Core\DataTransformer\BooleanToStringTransformer',
            $viewTransformers[0]
        );
    }

    public function testApiType()
    {
        $viewTransformers = $this->createCheckboxForm(array('type' => BooleanExtension::TYPE_API))
            ->getConfig()
            ->getViewTransformers();

        $this->assertCount(1, $viewTransformers);
        $this->assertInstanceOf(
            'FOS\RestBundle\Form\Transformer\BooleanTypeToBooleanTransformer',
            $viewTransformers[0]
        );
    }

    /**
     * @param bool  $expected
     * @param mixed $data
     *
     * @dataProvider validProvider
     */
    public function testValidSubmittedData($expected, $data)
    {
        $form = $this->createCheckboxForm(array('type' => BooleanExtension::TYPE_API));
        $form->submit($data);

        $this->assertSame($expected, $form->getData());
    }

    /**
     * @param mixed $data
     *
     * @dataProvider invalidProvider
     */
    public function testInvalidSubmittedData($data)
    {
        $form = $this->createCheckboxForm(array('type' => BooleanExtension::TYPE_API));
        $form->submit($data);

        $this->assertNull($form->getData());
    }

    public static function validProvider()
    {
        return array(
            array(true, true),
            array(true, 1),
            array(true, '1'),
            array(true, 'true'),
            array(true, 'yes'),
            array(true, 'on'),
            array(false, false),
            array(false, 0),
            array(false, '0'),
            array(false, 'false'),
            array(false, 'no'),
            array(false, 'off'),
            array(false, ''),
            array(false, null),
    );
    }

    public static function invalidProvider()
    {
        return array(
            array('foo'),
            array(1.2),
            array(new \stdClass()),
            array(array('foo' => 'bar')),
        );
    }

    /**
     * @param array $options
     *
     * @return FormInterface
     */
    private function createCheckboxForm(array $options = array())
    {
        if (method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')) {
            $type = 'Symfony\Component\Form\Extension\Core\Type\CheckboxType';
        } else {
            $type = 'checkbox';
        }

        return $this->formFactory->create($type, null, $options);
    }
}
