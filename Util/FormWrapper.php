<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Util;

use Symfony\Component\Form\FormInterface;

/**
 * Fix for https://github.com/FriendsOfSymfony/FOSRestBundle/pull/1358#issuecomment-191206821
 * to be removed once a patch is applied on symfony.
 *
 * @internal do not use this class in your own code.
 */
class FormWrapper
{
    public $form;

    public function __construct(FormInterface $form)
    {
        $this->form = $form;
    }
}
