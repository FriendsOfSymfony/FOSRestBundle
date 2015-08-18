<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Controller\Annotations;

/**
 * {@inheritdoc}
 *
 * @author Ener-Getick <egetick@gmail.Com>
 */
abstract class ScalarParam extends Param
{
    /** @var bool */
    public $array = false;
    /** @var bool */
    public $allowBlank = true;
}
