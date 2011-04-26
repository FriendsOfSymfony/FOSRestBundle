<?php

namespace FOS\RestBundle\Serializer\Normalizer;

use Symfony\Component\Serializer\SerializerInterface,
    Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

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
 * Converts a ConstraintViolationList instance to an array of errors
 *
 * @author Lukas K. Smith <smith@pooteeweet.org>
 */
class ValidatorConstraintViolationListNormalizer extends AbstractNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format, $properties = null)
    {
        $errors = array();

        $violations = $object->getIterator();
        foreach ($violations as $violation) {
            $errors[] = $violation->getMessage();
        }

        return $errors;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null)
    {
        throw new \Exception('Not implemented');
    }

    /**
     * Checks if the given class is a ConstraintViolationList
     *
     * @param ReflectionClass $class  A ReflectionClass instance of the class
     *                                to serialize into or from.
     * @param string          $format The format being (de-)serialized from or into.
     * @return Boolean Whether the class has any getters.
     */
    public function supports(\ReflectionClass $class, $format = null)
    {
        return $class->name === 'Symfony\Component\Validator\ConstraintViolationList';
    }
}
