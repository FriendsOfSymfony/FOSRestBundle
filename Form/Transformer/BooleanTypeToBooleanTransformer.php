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

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @author GeLo <geloen.eric@gmail.com>
 */
class BooleanTypeToBooleanTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if ($value === null) {
            return;
        }

        if (!is_bool($value)) {
            throw new TransformationFailedException(sprintf(
                'The boolean type expects a boolean or null value, got "%s"',
                is_object($value) ? get_class($value) : gettype($value)
            ));
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (in_array($value, array('1', 'true', 'on', 'yes'), true)) {
            return true;
        }

        if (in_array($value, array('0', 'false', 'off', 'no', '', null), true)) {
            return false;
        }

        throw new TransformationFailedException('The boolean type could not be reverse transformed.');
    }
}
