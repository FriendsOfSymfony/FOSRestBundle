<?php

namespace FOS\RestBundle\Serializer\Normalizer;

use Symfony\Component\Serializer\SerializerInterface,
    Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer,
    Symfony\Component\Validator\ConstraintViolation;

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
 * Converts a ConstraintViolation instance to an array of errors
 *
 * @author Lukas K. Smith <smith@pooteeweet.org>
 */
class ValidatorConstraintViolationNormalizer extends SerializerAwareNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null)
    {
        return array(
          "propertyPath" => $object->getPropertyPath(),
          "message" => $object->getMessage()
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
     * Checks whether the given class is supported for normalization by this normalizer
     *
     * @param mixed   $data   Data to normalize.
     * @param string  $format The format being (de-)serialized from or into.
     * @return Boolean
     * @api
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ConstraintViolation;
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
