<?php

namespace FOS\RestBundle\Serializer;

use FOS\RestBundle\Util\ExceptionWrapper;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\VisitorInterface;

class ExceptionWrapperSerializeHandler implements SubscribingHandlerInterface
{
    /**
     * @return array
     */
    public static function getSubscribingMethods()
    {
        return array(
            array(
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => 'FOS\\RestBundle\\Util\\ExceptionWrapper',
                'method' => 'serializeToJson'
            )
        );
    }

    /**
     * @param VisitorInterface $visitor
     * @param ExceptionWrapper $value
     * @param array $type
     * @param Context $context
     * @return string
     */
    public function serializeToJson(
        VisitorInterface $visitor,
        ExceptionWrapper $value,
        array $type,
        Context $context
    ) {
        $type['name'] = 'array';
        $data = array(
            'code' => $value->getCode(),
            'message' => $value->getMessage(),
            'errors' => $value->getErrors(),
        );
        return $visitor->visitArray($data, $type, $context);
    }
}
