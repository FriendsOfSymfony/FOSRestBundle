<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Form\Extension;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class DisableCSRFExtension.
 *
 * @author Grégoire Pineau
 */
class DisableCSRFExtension extends AbstractTypeExtension
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;
    /**
     * @var string
     */
    private $role;
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(TokenStorageInterface $tokenStorage, $role, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->tokenStorage = $tokenStorage;
        $this->role = $role;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        if (!$this->tokenStorage->getToken()) {
            return;
        }

        if (!$this->authorizationChecker->isGranted($this->role)) {
            return;
        }

        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }

    public function getExtendedType()
    {
        return method_exists(AbstractType::class, 'getBlockPrefix')
            ? FormType::class
            : 'form' // SF <2.8 BC
            ;
    }
}
