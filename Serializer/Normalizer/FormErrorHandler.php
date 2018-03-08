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
use JMS\Serializer\Handler\FormErrorHandler as JMSFormErrorHandler;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\XmlSerializationVisitor;
use Symfony\Component\Form\Form;
use JMS\Serializer\YamlSerializationVisitor;

/**
 * Extend the JMS FormErrorHandler to include more informations when using the ViewHandler.
 */
class FormErrorHandler implements SubscribingHandlerInterface
{
    private $formErrorHandler;

    public function __construct(JMSFormErrorHandler $formErrorHandler)
    {
        $this->formErrorHandler = $formErrorHandler;
    }

    public static function getSubscribingMethods()
    {
        return JMSFormErrorHandler::getSubscribingMethods();
    }

    public function serializeFormToXml(XmlSerializationVisitor $visitor, Form $form, array $type, Context $context = null)
    {
        if ($context) {
            $statusCode = $context->attributes->get('status_code');
            if ($statusCode->isDefined()) {
                if (null === $visitor->document) {
                    $visitor->document = $visitor->createDocument(null, null, true);
                }

                $codeNode = $visitor->document->createElement('code');
                $visitor->getCurrentNode()->appendChild($codeNode);
                $codeNode->appendChild($context->getNavigator()->accept($statusCode->get(), null, $context));

                $messageNode = $visitor->document->createElement('message');
                $visitor->getCurrentNode()->appendChild($messageNode);
                $messageNode->appendChild($context->getNavigator()->accept('Validation Failed', null, $context));

                $errorsNode = $visitor->document->createElement('errors');
                $visitor->getCurrentNode()->appendChild($errorsNode);
                $visitor->setCurrentNode($errorsNode);
                $this->formErrorHandler->serializeFormToXml($visitor, $form, $type);
                $visitor->revertCurrentNode();

                return;
            }
        }

        return $this->formErrorHandler->serializeFormToXml($visitor, $form, $type);
    }

    public function serializeFormToJson(JsonSerializationVisitor $visitor, Form $form, array $type, Context $context = null)
    {
        $isRoot = null === $visitor->getRoot();
        $result = $this->adaptFormArray($this->formErrorHandler->serializeFormToJson($visitor, $form, $type), $context);

        if ($isRoot) {
            $visitor->setRoot($result);
        }

        return $result;
    }

    public function serializeFormToYml(YamlSerializationVisitor $visitor, Form $form, array $type, Context $context = null)
    {
        $isRoot = null === $visitor->getRoot();
        $result = $this->adaptFormArray($this->formErrorHandler->serializeFormToYml($visitor, $form, $type), $context);

        if ($isRoot) {
            $visitor->setRoot($result);
        }

        return $result;
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->formErrorHandler, $name], $arguments);
    }

    private function adaptFormArray(\ArrayObject $serializedForm, Context $context = null)
    {
        $statusCode = $this->getStatusCode($context);
        if (null !== $statusCode) {
            return [
                'code' => $statusCode,
                'message' => 'Validation Failed',
                'errors' => $serializedForm,
            ];
        }

        return $serializedForm;
    }

    private function getStatusCode(Context $context = null)
    {
        if (null === $context) {
            return;
        }

        $statusCode = $context->attributes->get('status_code');
        if ($statusCode->isDefined()) {
            return $statusCode->get();
        }
    }
}
