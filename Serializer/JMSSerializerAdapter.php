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

use FOS\RestBundle\Context\Context;
use JMS\Serializer\Context as JMSContext;
use JMS\Serializer\ContextFactory\DeserializationContextFactoryInterface;
use JMS\Serializer\ContextFactory\SerializationContextFactoryInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;

/**
 * Adapter to plug the JMS serializer into the FOSRestBundle Serializer API.
 *
 * @author Christian Flothmann <christian.flothmann@xabbuh.de>
 */
final class JMSSerializerAdapter implements Serializer
{
    /**
     * @internal
     */
    const SERIALIZATION = 0;

    /**
     * @internal
     */
    const DESERIALIZATION = 1;

    private $serializer;
    private $serializationContextFactory;
    private $deserializationContextFactory;

    public function __construct(
        SerializerInterface $serializer,
        SerializationContextFactoryInterface $serializationContextFactory = null,
        DeserializationContextFactoryInterface $deserializationContextFactory = null
    ) {
        $this->serializer = $serializer;
        $this->serializationContextFactory = $serializationContextFactory;
        $this->deserializationContextFactory = $deserializationContextFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize($data, string $format, Context $context): string
    {
        $context = $this->convertContext($context, self::SERIALIZATION);

        return $this->serializer->serialize($data, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function deserialize(string $data, string $type, string $format, Context $context)
    {
        $context = $this->convertContext($context, self::DESERIALIZATION);

        return $this->serializer->deserialize($data, $type, $format, $context);
    }

    private function convertContext(Context $context, int $direction): JMSContext
    {
        if (self::SERIALIZATION === $direction) {
            $jmsContext = $this->serializationContextFactory
                ? $this->serializationContextFactory->createSerializationContext()
                : SerializationContext::create();
        } else {
            $jmsContext = $this->deserializationContextFactory
                ? $this->deserializationContextFactory->createDeserializationContext()
                : DeserializationContext::create();
        }

        foreach ($context->getAttributes() as $key => $value) {
            $jmsContext->setAttribute($key, $value);
        }

        if (null !== $context->getVersion()) {
            $jmsContext->setVersion($context->getVersion());
        }
        if (null !== $context->getGroups()) {
            $jmsContext->setGroups($context->getGroups());
        }
        if (true === $context->isMaxDepthEnabled()) {
            $jmsContext->enableMaxDepthChecks();
        }
        if (null !== $context->getSerializeNull()) {
            $jmsContext->setSerializeNull($context->getSerializeNull());
        }

        foreach ($context->getExclusionStrategies() as $strategy) {
            $jmsContext->addExclusionStrategy($strategy);
        }

        return $jmsContext;
    }
}
