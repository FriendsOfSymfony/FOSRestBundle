<?php
namespace FOS\RestBundle\Normalizer;

use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Form\FormView;
    
class formNormalizer extends AbstractNormalizer
{

    private $className = '';

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format, $properties = null)
    {
        $attributes = array();

        foreach($object->all() as $key => $attributeValue) {
            if("form" == $key && $attributeValue instanceof FormView)
            {
                $children = array();
                foreach ($attributeValue->getChildren() as $formKey => $childValue) {
                    $children[$formKey] = $this->serializer->normalize($childValue, $format);
                }
                $attributeValue = $children;
            } elseif ($this->serializer->isStructuredType($attributeValue)) {
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
        return "Form denormalization not yet supported";
    }

    /**
     * Returns true all the time...this is just a rapid object to handle this
     * 
     * @param  string $format The format being (de-)serialized from or into.
     * @return Boolean Whether the class has any getters.
     */
    public function supports(\ReflectionClass $class, $format = null)
    {
        if ($class->getName() === 'Symfony\Component\Form\FormView') {
            $this->className = $class->getName();
            return true;
        }
        return false;
    }
}