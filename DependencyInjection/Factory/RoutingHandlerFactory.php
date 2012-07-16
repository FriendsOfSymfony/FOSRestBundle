<?php

namespace FOS\RestBundle\DependencyInjection\Factory;

use JMS\SerializerBundle\DependencyInjection\HandlerFactoryInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RoutingHandlerFactory implements HandlerFactoryInterface
{
    public function getConfigKey()
    {
        return 'routing';
    }

    public function getType(array $config)
    {
        return self::TYPE_SERIALIZATION | self::TYPE_DESERIALIZATION;
    }

    public function addConfiguration(ArrayNodeDefinition $builder)
    {
    }

    public function getHandlerId(ContainerBuilder $container, array $config)
    {
        return 'fos_rest.serializer.handler.routing';
    }
}
