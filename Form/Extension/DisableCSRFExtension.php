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

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class DisableCSRFExtension.
 *
 * @author Grégoire Pineau
 *
 * @internal
 */
class DisableCSRFExtension extends AbstractTypeExtension
{
    private $tokenStorage;
    private $role;
    private $authorizationChecker;

    public function __construct(TokenStorageInterface $tokenStorage, string $role, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->tokenStorage = $tokenStorage;
        $this->role = $role;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function configureOptions(OptionsResolver $resolver): void
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

    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }
}
