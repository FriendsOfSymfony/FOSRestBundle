<?php
namespace FOS\RestBundle\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer,
    Symfony\Component\Form\FormView;

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
 * This Normalizer gets the data from the FormView::all() method.
 *
 * If it finds a FormView child it will recurse 
 *
 * @author John Wards <johnwards@gmail.com>
 */
class FormViewNormalizer extends SerializerAwareNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null)
    {
        $attributes = array();

        foreach ($object->all() as $key => $attributeValue) {
            if ("form" == $key && $attributeValue instanceof FormView) {
                $children = array();
                foreach ($attributeValue->getChildren() as $formKey => $childValue) {
                    $children[$formKey] = $this->serializer->normalize($childValue, $format);
                }
                $attributeValue = $children;
            } else if ($this->serializer->isStructuredType($attributeValue)) {
                $attributeValue = $this->serializer->normalize($attributeValue, $format);
            }
            $attributes[$key] = $attributeValue;
        }
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
        if ($data instanceof FormView) {
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
