<?php

namespace FOS\RestBundle\Serializer\Handler;

use JMS\SerializerBundle\Serializer\VisitorInterface;
use JMS\SerializerBundle\Serializer\Handler\SerializationHandlerInterface;
use Symfony\Component\Routing\RouterInterface;
use Doctrine\Common\Annotations\Reader;
use FOS\RestBundle\Serializer\Annotations\Url;

class RoutingHandler implements SerializationHandlerInterface
{
    private $router;
    private $reader;

    public function __construct(RouterInterface $router, Reader $reader)
    {
        $this->router = $router;
        $this->reader = $reader;
    }

    /**
     * Serialize if there is Url annotation
     *
     * @param VisitorInterface
     * @param mixed $data
     * @param mixed $type
     * @param boolean $visited
     */
    public function serialize(VisitorInterface $visitor, $data, $type, &$visited)
    {
        $refl = new \ReflectionClass($data);
        $classAnnotations = $this->reader->getClassAnnotations($refl);
        foreach ($classAnnotations as $annotation) {
            if ($annotation instanceof Url) {
                $params = array();
                foreach ($annotation->params as $param) {
                    $value = $refl->getMethod('get'.ucwords($param->field))->invoke($data);
                    $params[$param->key] = $value;
                }
                $url = $this->router->generate($annotation->routeName, $params);
                $refl->getMethod('set'.ucwords($annotation->field))->invoke($data, $url);
            }
        }
    }
}

