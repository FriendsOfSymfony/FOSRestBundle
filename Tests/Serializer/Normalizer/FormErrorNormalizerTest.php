<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Serializer\Normalizer;

use FOS\RestBundle\Serializer\Normalizer\FormErrorNormalizer;
use Symfony\Component\Translation\Translator;

class FormErrorNormalizerTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultTranslationDomain()
    {
        /** @var Translator|\PHPUnit_Framework_MockObject_MockObject $translator */
        $translator = $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')->getMock();

        $normalizer = new FormErrorNormalizer($translator);

        $translator->expects($this->once())
            ->method('trans')
            ->with(
                $this->equalTo('error!'),
                $this->equalTo([]),
                $this->equalTo('validators')
            );

        $formError = $this->getMockBuilder('Symfony\Component\Form\FormError')->disableOriginalConstructor()->getMock();
        $formError->expects($this->once())->method('getMessageTemplate')->willReturn('error!');
        $formError->expects($this->once())->method('getMessagePluralization')->willReturn(null);
        $formError->expects($this->once())->method('getMessageParameters')->willReturn([]);

        $this->invokeMethod($normalizer, 'getErrorMessage', [$formError]);
    }

    public function testDefaultTranslationDomainWithPluralTranslation()
    {
        /** @var Translator|\PHPUnit_Framework_MockObject_MockObject $translator */
        $translator = $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')->getMock();

        $normalizer = new FormErrorNormalizer($translator);

        $translator->expects($this->once())
            ->method('transChoice')
            ->with(
                $this->equalTo('error!'),
                $this->equalTo(0),
                $this->equalTo([]),
                $this->equalTo('validators')
            );

        $formError = $this->getMockBuilder('Symfony\Component\Form\FormError')->disableOriginalConstructor()->getMock();
        $formError->expects($this->once())->method('getMessageTemplate')->willReturn('error!');
        $formError->expects($this->exactly(2))->method('getMessagePluralization')->willReturn(0);
        $formError->expects($this->once())->method('getMessageParameters')->willReturn([]);

        $this->invokeMethod($normalizer, 'getErrorMessage', [$formError]);
    }

    public function testCustomTranslationDomain()
    {
        /** @var Translator|\PHPUnit_Framework_MockObject_MockObject $translator */
        $translator = $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')->getMock();

        $normalizer = new FormErrorNormalizer($translator, 'custom_domain');

        $translator->expects($this->once())
            ->method('trans')
            ->with(
                $this->equalTo('error!'),
                $this->equalTo([]),
                $this->equalTo('custom_domain')
            );

        $formError = $this->getMockBuilder('Symfony\Component\Form\FormError')->disableOriginalConstructor()->getMock();
        $formError->expects($this->once())->method('getMessageTemplate')->willReturn('error!');
        $formError->expects($this->once())->method('getMessagePluralization')->willReturn(null);
        $formError->expects($this->once())->method('getMessageParameters')->willReturn([]);

        $this->invokeMethod($normalizer, 'getErrorMessage', [$formError]);
    }

    public function testCustomTranslationDomainWithPluralTranslation()
    {
        /** @var Translator|\PHPUnit_Framework_MockObject_MockObject $translator */
        $translator = $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')->getMock();

        $normalizer = new FormErrorNormalizer($translator, 'custom_domain');

        $translator->expects($this->once())
            ->method('transChoice')
            ->with(
                $this->equalTo('error!'),
                $this->equalTo(0),
                $this->equalTo([]),
                $this->equalTo('custom_domain')
            );

        $formError = $this->getMockBuilder('Symfony\Component\Form\FormError')->disableOriginalConstructor()->getMock();
        $formError->expects($this->once())->method('getMessageTemplate')->willReturn('error!');
        $formError->expects($this->exactly(2))->method('getMessagePluralization')->willReturn(0);
        $formError->expects($this->once())->method('getMessageParameters')->willReturn([]);

        $this->invokeMethod($normalizer, 'getErrorMessage', [$formError]);
    }

    private function invokeMethod($object, $method, array $args = [])
    {
        $reflectionMethod = new \ReflectionMethod($object, $method);
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod->invokeArgs($object, $args);
    }
}
