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
    Symfony\Component\Form\FormView;

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
            if ('form' == $key && $attributeValue instanceof FormView) {
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
        return $data instanceof FormView;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return false;
    }
}
