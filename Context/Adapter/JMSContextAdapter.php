<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Context\Adapter;

use FOS\RestBundle\Context\ContextInterface;
use FOS\RestBundle\Context\GroupableContextInterface;
use FOS\RestBundle\Context\MaxDepthContextInterface;
use FOS\RestBundle\Context\SerializeNullContextInterface;
use FOS\RestBundle\Context\VersionableContextInterface;
use JMS\Serializer\Context as JMSContext;
use JMS\Serializer\DeserializationContext as JMSDeserializationContext;
use JMS\Serializer\SerializationContext as JMSSerializationContext;
use JMS\Serializer\SerializerInterface as JMSSerializerInterface;

/**
 * {@inheritdoc}
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
class JMSContextAdapter implements SerializationContextAdapterInterface, DeserializationContextAdapterInterface, SerializerAwareInterface
{
    /**
     * @var mixed
     */
    private $serializer;

    /**
     * {@inheritdoc}
     */
    public function convertSerializationContext(ContextInterface $context)
    {
        if (!$this->supportsSerialization($context)) {
            throw new \LogicException(sprintf('%s can\'t convert this serialization context.', get_class($this)));
        }

        $newContext = JMSSerializationContext::create();
        $this->fillContext($context, $newContext);

        return $newContext;
    }

    /**
     * {@inheritdoc}
     */
    public function convertDeserializationContext(ContextInterface $context)
    {
        if (!$this->supportsDeserialization($context)) {
            throw new \LogicException(sprintf('%s can\'t convert this deserialization context.', get_class($this)));
        }

        $newContext = JMSDeserializationContext::create();
        if ($context instanceof MaxDepthContextInterface && null !== $context->getMaxDepth()) {
            for ($i = 0; $i < $context->getMaxDepth(); ++$i) {
                $newContext->increaseDepth();
            }
        }

        $this->fillContext($context, $newContext);

        return $newContext;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsSerialization(ContextInterface $context)
    {
        return $this->serializer instanceof JMSSerializerInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDeserialization(ContextInterface $context)
    {
        return $this->serializer instanceof JMSSerializerInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer($serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Fill a jms context.
     *
     * @param ContextInterface $context
     * @param JMSContext       $newContext
     *
     * @return JMSContext
     */
    private function fillContext(ContextInterface $context, JMSContext $newContext)
    {
        foreach ($context->getAttributes() as $key => $value) {
            $newContext->attributes->set($key, $value);
        }

        if ($context instanceof VersionableContextInterface && null !== $context->getVersion()) {
            $newContext->setVersion($context->getVersion());
        }
        if ($context instanceof GroupableContextInterface) {
            $groups = $context->getGroups();
            if (!empty($groups)) {
                $newContext->setGroups($context->getGroups());
            }
        }
        if ($context instanceof MaxDepthContextInterface && null !== $context->getMaxDepth()) {
            $newContext->enableMaxDepthChecks();
        }
        if ($context instanceof SerializeNullContextInterface && null !== $context->getSerializeNull()) {
            $newContext->setSerializeNull($context->getSerializeNull());
        }

        return $newContext;
    }
}
