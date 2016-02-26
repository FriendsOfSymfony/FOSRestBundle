<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Serializer\Normalizer;

use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\XmlSerializationVisitor;

class ExceptionHandler extends AbstractExceptionNormalizer implements SubscribingHandlerInterface
{
    /**
     * @return array
     */
    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => \Exception::class,
                'method' => 'serializeToJson',
            ],
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'xml',
                'type' => \Exception::class,
                'method' => 'serializeToXml',
            ],
        ];
    }

    /**
     * @param JsonSerializationVisitor $visitor
     * @param Exception                $exception
     * @param array                    $type
     * @param Context                  $context
     *
     * @return array
     */
    public function serializeToJson(
        JsonSerializationVisitor $visitor,
        \Exception $exception,
        array $type,
        Context $context
    ) {
        $data = $this->convertToArray($exception, $context);

        return $visitor->visitArray($data, $type, $context);
    }

    /**
     * @param XmlSerializationVisitor $visitor
     * @param Exception               $exception
     * @param array                   $type
     * @param Context                 $context
     */
    public function serializeToXml(
        XmlSerializationVisitor $visitor,
        \Exception $exception,
        array $type,
        Context $context
    ) {
        $data = $this->convertToArray($exception, $context);

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
     * @param \Exception $exception
     *
     * @return array
     */
    protected function convertToArray(\Exception $exception, Context $context)
    {
        $data = [];

        $templateData = $context->attributes->get('template_data');
        if ($templateData->isDefined()) {
            $data['code'] = $statusCode = $templateData->get()['status_code'];
        }

        $data['message'] = $this->getExceptionMessage($exception, isset($statusCode) ? $statusCode : null);

        return $data;
    }
}
