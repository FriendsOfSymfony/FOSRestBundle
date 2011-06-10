<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer,
    Symfony\Component\Form\FormError;

/**
 * This Normalizer turns the FormError classes into strings
 *
 * @author John Wards <johnwards@gmail.com>
 */
class FormErrorNormalizer extends SerializerAwareNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null)
    {
        return str_replace(
            array_keys($object->getMessageParameters()), $object->getMessageParameters(), $object->getMessageTemplate()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null)
    {
        throw new \BadMethodCallException('Not supported');
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof FormError;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return false;
    }
}
