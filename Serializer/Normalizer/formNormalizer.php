<?php
namespace FOS\RestBundle\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

use Symfony\Component\Form\Form;
    
class formNormalizer extends AbstractNormalizer
{

    private $invalidMethods = array("getParent", "getRoot");

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format, $properties = null)
    {
        $propertyMap = (null === $properties) ? null : array_flip(array_map('strtolower', $properties));

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

                if (null === $propertyMap || isset($propertyMap[$attributeName])) {
                    $attributeValue = $method->invoke($object);
                    if ($this->serializer->isStructuredType($attributeValue)) {
                        $attributeValue = $this->serializer->normalize($attributeValue, $format);
                    }

                    $attributes[$attributeName] = $attributeValue;
                }
            }
        }

        return $attributes;
    }

    /**
     * Checks if a method's name is get.* and can be called without parameters.
     *
     * @param ReflectionMethod $method the method to check
     * @return Boolean whether the method is a getter.
     */
    private function isValidMethod(\ReflectionMethod $method)
    {
        return (
            (0 === strpos($method->getName(), 'get') || 0 === strpos($method->getName(), 'is') || 0 === strpos($method->getName(), 'has')) &&
            3 < strlen($method->getName()) &&
            0 === $method->getNumberOfRequiredParameters() && !in_array($method->getName(),$this->invalidMethods)
        );
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
        if ($class->getName() === 'Symfony\Component\Form\Form') {
            return true;
        }
        return false;
    }
}