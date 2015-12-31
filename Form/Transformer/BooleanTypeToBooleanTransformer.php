<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Form\Transformer;

use FOS\RestBundle\Form\Type\BooleanType;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @author Florent SEVESTRE
 */
class BooleanTypeToBooleanTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (true === $value || BooleanType::VALUE_TRUE === (int) $value) {
            return BooleanType::VALUE_TRUE;
        }

        return BooleanType::VALUE_FALSE;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (BooleanType::VALUE_TRUE === (int) $value) {
            return true;
        }

        return false;
    }
}
