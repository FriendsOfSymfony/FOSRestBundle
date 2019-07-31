<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Functional\Bundle\TestBundle\Controller;

use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Controller to test serialization of various errors and exceptions.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class SerializerErrorController extends AbstractController
{
    public function logicExceptionAction()
    {
        throw new \LogicException('Something bad happened.');
    }

    public function unknownExceptionAction()
    {
        throw new \OutOfBoundsException('Unknown exception message.');
    }

    /**
     * @View
     */
    public function invalidFormAction()
    {
        $form = $this->createFormBuilder(null, [
            'csrf_protection' => false,
        ])->add('name', TextType::class, [
            'constraints' => [new NotBlank()],
        ])->getForm();

        $form->submit([]);

        return $form;
    }
}
