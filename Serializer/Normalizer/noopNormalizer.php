<?php
namespace FOS\RestBundle\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

/**
 * This handles unknown objects gracefully
 */
class noopNormalizer extends AbstractNormalizer
{

    private $className = '';

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format, $properties = null)
    {
        return array("Can not normalize ".$this->className);
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null)
    {
        return "Can not denormalize ".$this->className;
    }

    /**
     * Returns true all the time...this is just a rapid object to handle this
     * 
     * @param  string $format The format being (de-)serialized from or into.
     * @return Boolean Whether the class has any getters.
     */
    public function supports(\ReflectionClass $class, $format = null)
    {
        $this->className = $class->getName();
        return true;
    }
}