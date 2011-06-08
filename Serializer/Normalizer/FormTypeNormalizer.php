<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer,
    Symfony\Component\Form\FormTypeInterface;

/**
 * This Normalizer gets some useful information from a FormTypeInterface instance
 *
 * @author John Wards <johnwards@gmail.com>
 */
class FormTypeNormalizer extends SerializerAwareNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null)
    {
        $attributes = array();
        $attributes["name"] = $object->getName();
        $attributes["parent"] = $object->getParent(array());
        $attributes["default_options"] = $object->getDefaultOptions(array());
        return $attributes;
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
        return $data instanceof FormTypeInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return false;
    }
}
