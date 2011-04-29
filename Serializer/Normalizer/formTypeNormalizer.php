<?php
namespace FOS\RestBundle\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

/**
 * This handles unknown objects gracefully
 */
class formTypeNormalizer extends AbstractNormalizer
{

    private $className = '';

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format, $properties = null)
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
        return "Can not denormalize ".$this->className;
    }

    /**
     * 
     *
     * @param  string $format The format being (de-)serialized from or into.
     * @return Boolean Whether the class has any getters.
     */
    public function supports(\ReflectionClass $class, $format = null)
    {

        if($class->implementsInterface("Symfony\\Component\\Form\\FormTypeInterface")) {
            return true;
        }

        return false;
    }
}