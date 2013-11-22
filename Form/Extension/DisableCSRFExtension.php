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
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Class DisableCSRFExtension
 *
 * @author Grégoire Pineau
 */
class DisableCSRFExtension extends AbstractTypeExtension
{
    private $securityContext;
    private $role;

    public function __construct(SecurityContextInterface $securityContext, $role)
    {
        $this->securityContext = $securityContext;
        $this->role = $role;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        if (!$this->securityContext->getToken()) {
            return;
        }

        if (!$this->securityContext->isGranted($this->role)) {
            return;
        }

        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }

    public function getExtendedType()
    {
        return 'form';
    }
}
