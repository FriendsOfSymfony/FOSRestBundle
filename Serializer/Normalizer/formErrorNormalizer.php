<?php
namespace FOS\RestBundle\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class formErrorNormalizer extends AbstractNormalizer
{

    private $className = '';

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format, $properties = null)
    {
        return str_replace(array_keys($object->getMessageParameters()), $object->getMessageParameters(), $object->getMessageTemplate());
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null)
    {
        return "Form error denormalization not yet supported";
    }

    /**
     * Returns true all the time...this is just a rapid object to handle this
     * 
     * @param  string $format The format being (de-)serialized from or into.
     * @return Boolean Whether the class has any getters.
     */
    public function supports(\ReflectionClass $class, $format = null)
    {
        if ($class->getName() === 'Symfony\Component\Form\FormError') {
            $this->className = $class->getName();
            return true;
        }
        return false;
    }
}