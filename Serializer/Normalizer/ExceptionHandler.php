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
use JMS\Serializer\GraphNavigatorInterface;
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
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => \Exception::class,
                'method' => 'serializeToJson',
            ],
            [
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format' => 'xml',
                'type' => \Exception::class,
                'method' => 'serializeToXml',
            ],
        ];
    }

    /**
     * @param JsonSerializationVisitor $visitor
     * @param \Exception               $exception
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
     * @param \Exception              $exception
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

        $document = $visitor->getDocument(true);

        if (!$visitor->getCurrentNode()) {
            $visitor->createRoot();
        }

        foreach ($data as $key => $value) {
            $entryNode = $document->createElement($key);
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
     * @param Context    $context
     *
     * @return array
     */
    protected function convertToArray(\Exception $exception, Context $context)
    {
        $data = [];

        if ($context->hasAttribute('template_data')) {
            $templateData = $context->getAttribute('template_data');
            if (array_key_exists('status_code', $templateData)) {
                $data['code'] = $statusCode = $templateData['status_code'];
            }
        }

        $data['message'] = $this->getExceptionMessage($exception, isset($statusCode) ? $statusCode : null);

        return $data;
    }
}
