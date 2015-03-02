<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Serializer;

use FOS\RestBundle\Util\ExceptionWrapper;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\XmlSerializationVisitor;

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
                'method' => 'serializeToJson',
            ),
            array(
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'xml',
                'type' => 'FOS\\RestBundle\\Util\\ExceptionWrapper',
                'method' => 'serializeToXml',
            ),
        );
    }

    /**
     * @param JsonSerializationVisitor $visitor
     * @param ExceptionWrapper         $wrapper
     * @param array                    $type
     * @param Context                  $context
     *
     * @return array
     */
    public function serializeToJson(
        JsonSerializationVisitor $visitor,
        ExceptionWrapper $wrapper,
        array $type,
        Context $context
    ) {
        $data = $this->convertToArray($wrapper);

        return $visitor->visitArray($data, $type, $context);
    }

    /**
     * @param XmlSerializationVisitor $visitor
     * @param ExceptionWrapper        $wrapper
     * @param array                   $type
     * @param Context                 $context
     */
    public function serializeToXml(
        XmlSerializationVisitor $visitor,
        ExceptionWrapper $wrapper,
        array $type,
        Context $context
    ) {
        $data = $this->convertToArray($wrapper);

        if (null === $visitor->document) {
            $visitor->document = $visitor->createDocument(null, null, true);
        }

        foreach ($data as $key => $value) {
            $entryNode = $visitor->document->createElement($key);
            $visitor->getCurrentNode()->appendChild($entryNode);
            $visitor->setCurrentNode($entryNode);

            $node = $context->getNavigator()->accept($value, null, $context);
            if (null !== $node) {
                $visitor->getCurrentNode()->appendChild($node);
            }

            $visitor->revertCurrentNode();
        }
    }

    /**
     * @param ExceptionWrapper $exceptionWrapper
     *
     * @return array
     */
    protected function convertToArray(ExceptionWrapper $exceptionWrapper)
    {
        return array(
            'code' => $exceptionWrapper->getCode(),
            'message' => $exceptionWrapper->getMessage(),
            'errors' => $exceptionWrapper->getErrors(),
        );
    }
}
