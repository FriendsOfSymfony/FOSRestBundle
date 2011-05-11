<?php
namespace FOS\RestBundle\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer,
    Symfony\Component\Form\Form;

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
 * This Normalizer is based on the GetSetNormalizer in core.
 *
 * It checks for invalid method names to get data from, it also checks "is" and "has" method names for data.
 *
 * @author John Wards <johnwards@gmail.com>
 */
class FormNormalizer extends SerializerAwareNormalizer
{
    /**
     * A list of invalid methods. These generally cause recursion or contain useless data.
     */
    private $invalidMethods = array("getParent", "getRoot");

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null)
    {
        $reflectionObject = new \ReflectionObject($object);
        $reflectionMethods = $reflectionObject->getMethods(\ReflectionMethod::IS_PUBLIC);

        $attributes = array();
        foreach ($reflectionMethods as $method) {
            if ($this->isValidMethod($method)) {
                if(0 === strpos($method->getName(), 'get')) {
                    $attributeName = strtolower(substr($method->getName(), 3));
                } else {
                    $attributeName = strtolower($method->getName());
                }

                $attributeValue = $method->invoke($object);
                if (null !== $attributeValue && !is_scalar($attributeValue)) {
                    $attributeValue = $this->serializer->normalize($attributeValue, $format);
                }

                $attributes[$attributeName] = $attributeValue;
            }
        }

        return $attributes;
    }

    /**
     * Checks if a method's name is get.*, is.*, has.*
     *
     * @param ReflectionMethod $method the method to check
     * @return Boolean whether the method is a getter, 'is-er' or 'has-er'.
     */
    private function isValidMethod(\ReflectionMethod $method)
    {
        return (
            (
                0 === strpos($method->getName(), 'get') ||
                0 === strpos($method->getName(), 'is') ||
                0 === strpos($method->getName(), 'has')
            ) &&
            3 < strlen($method->getName()) &&
            0 === $method->getNumberOfRequiredParameters() && !in_array($method->getName(),$this->invalidMethods)
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
        if ($data instanceof Form) {
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return false;
    }
}
