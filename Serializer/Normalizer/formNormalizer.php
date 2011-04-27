<?php
namespace FOS\RestBundle\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

    
class formNormalizer extends AbstractNormalizer
{

    private $className = '';

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format, $properties = null)
    {
        if($object->hasChildren())
        {
            foreach($object->getChildren() as $name => $child)
            {
                if($name=="name")
                {
                    var_dump($child);exit();
                }
            }
        }
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