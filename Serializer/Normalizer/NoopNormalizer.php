<?php

namespace FOS\RestBundle\Serializer\Normalizer;

use Symfony\Component\Serializer\SerializerInterface,
    Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;

/*
 * This file is part of the FOSRestBundle
 *
 * (c) Lukas Kahwe Smith <smith@pooteeweet.org>
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 * (c) Bulat Shakirzyanov <mallluhuct@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * This Normalizer basically just silences any Exceptions from missing normalizers
 *
 * @author John Wards <johnwards@gmail.com>
 */
class NoopNormalizer extends SerializerAwareNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null)
    {
        return array("Can not normalize ".get_class($object));
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null)
    {
        return "Cannot denormalize $class";
    }

    /**
     * Checks whether the given class is supported for normalization by this normalizer
     *
     * @param mixed   $data   Data to normalize.
     * @param string  $format The format being (de-)serialized from or into.
     * @return Boolean
     * @api
     */
    public function supportsNormalization($data, $format = null)
    {
        return true;
    }

    /**
     * Checks whether the given class is supported for denormalization by this normalizer
     *
     * @param mixed   $data   Data to denormalize from.
     * @param string  $type   The class to which the data should be denormalized.
     * @param string  $format The format being deserialized from.
     * @return Boolean
     * @api
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return false;
    }
}
