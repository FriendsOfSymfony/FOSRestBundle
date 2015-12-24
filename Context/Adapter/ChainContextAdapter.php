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

/**
 * {@inheritdoc}
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
class ChainContextAdapter implements SerializationContextAdapterInterface, DeserializationContextAdapterInterface, SerializerAwareInterface
{
    /**
     * @var array
     */
    private $serializationAdapters;
    /**
     * @var array
     */
    private $deserializationAdapters;
    /**
     * @var mixed
     */
    private $serializer;

    /**
     * Constructor.
     *
     * @param array $adapters
     */
    public function __construct(array $adapters)
    {
        $this->serializationAdapters = [];
        $this->deserializationAdapters = [];
        foreach ($adapters as $adapter) {
            $treated = false;
            if ($adapter instanceof SerializationContextAdapterInterface) {
                $this->serializationAdapters[] = $adapter;
                $treated = true;
            }
            if ($adapter instanceof DeserializationContextAdapterInterface) {
                $this->deserializationAdapters[] = $adapter;
                $treated = true;
            }
            if (!$treated) {
                throw new \LogicException(sprintf('%s::__construct parameter must be an array of FOS\RestBundle\Context\Adapter\SerializationContextAdapterInterface and FOS\RestBundle\Context\Adapter\DeserializationContextAdapterInterface.', get_class($this)));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function convertSerializationContext(ContextInterface $context)
    {
        foreach ($this->serializationAdapters as $adapter) {
            if ($adapter instanceof SerializerAwareInterface) {
                $adapter->setSerializer($this->serializer);
            }
            if ($adapter->supportsSerialization($context)) {
                return $adapter->convertSerializationContext($context);
            }
        }
        throw new \LogicException('This adapter can\'t convert this serialization context.');
    }

    /**
     * {@inheritdoc}
     */
    public function convertDeserializationContext(ContextInterface $context)
    {
        foreach ($this->deserializationAdapters as $adapter) {
            if ($adapter instanceof SerializerAwareInterface) {
                $adapter->setSerializer($this->serializer);
            }
            if ($adapter->supportsDeserialization($context)) {
                return $adapter->convertDeserializationContext($context);
            }
        }
        throw new \LogicException('This adapter can\'t convert this deserialization context.');
    }

    /**
     * {@inheritdoc}
     */
    public function supportsSerialization(ContextInterface $context)
    {
        foreach ($this->serializationAdapters as $adapter) {
            if ($adapter instanceof SerializerAwareInterface) {
                $adapter->setSerializer($this->serializer);
            }
            if ($adapter->supportsSerialization($context)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDeserialization(ContextInterface $context)
    {
        foreach ($this->deserializationAdapters as $adapter) {
            if ($adapter instanceof SerializerAwareInterface) {
                $adapter->setSerializer($this->serializer);
            }
            if ($adapter->supportsDeserialization($context)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer($serializer)
    {
        $this->serializer = $serializer;
    }
}
