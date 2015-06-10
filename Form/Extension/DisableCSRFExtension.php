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
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class DisableCSRFExtension
 *
 * @author Grégoire Pineau
 */
class DisableCSRFExtension extends AbstractTypeExtension
{
    /**
     * @var SecurityContextInterface|TokenStorageInterface
     */
    private $tokenStorage;
    private $role;
    private $authorizationChecker;

    public function __construct($tokenStorage, $role, $authorizationChecker = null)
    {
        $this->tokenStorage = $tokenStorage;
        $this->role = $role;
        $this->authorizationChecker = $authorizationChecker;

        if (!$tokenStorage instanceof TokenStorageInterface && !$tokenStorage instanceof SecurityContextInterface) {
            throw new \InvalidArgumentException('Argument 1 should be an instance of Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface or Symfony\Component\Security\Core\SecurityContextInterface');
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        if ($this->authorizationChecker instanceof AuthorizationCheckerInterface) {
            if (!$this->tokenStorage->getToken()) {
                return;
            }

            if (!$this->authorizationChecker->isGranted($this->role)) {
                return;
            }
        } else {
            if (!$this->tokenStorage->getToken()) {
                return;
            }

            if (!$this->tokenStorage->isGranted($this->role)) {
                return;
            }
        }

        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }

    // BC for < 2.7
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->configureOptions($resolver);
    }

    public function getExtendedType()
    {
        return 'form';
    }
}
