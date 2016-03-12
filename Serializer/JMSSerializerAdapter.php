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
use JMS\Serializer\DeserializationContext as JMSDeserializationContext;
use JMS\Serializer\SerializationContext as JMSSerializationContext;
use JMS\Serializer\SerializerInterface;

/**
 * Adapter to plug the JMS serializer into the FOSRestBundle Serializer API.
 *
 * @author Christian Flothmann <christian.flothmann@xabbuh.de>
 */
class JMSSerializerAdapter implements Serializer
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

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize($data, $format, $context = null)
    {
        $context = $this->convertContext($context, self::SERIALIZATION);

        return $this->serializer->serialize($data, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function deserialize($data, $type, $format, $context = null)
    {
        $context = $this->convertContext($context, self::DESERIALIZATION);

        return $this->serializer->deserialize($data, $type, $format, $context);
    }

    /**
     * @param mixed $context
     *
     * @return JMSContext
     */
    private function convertContext($context, $direction)
    {
        if ($context instanceof JMSContext) {
            @trigger_error(sprintf('Support of %s is deprecated since version 1.8 and will be removed in 2.0. You should use FOS\RestBundle\Context\Context instead.', get_class($context)), E_USER_DEPRECATED);
            $jmsContext = $context;
        } elseif ($context instanceof Context) {
            if ($direction === self::SERIALIZATION) {
                $jmsContext = JMSSerializationContext::create();
            } else {
                $jmsContext = JMSDeserializationContext::create();
                $maxDepth = $context->getMaxDepth();
                if (null !== $maxDepth) {
                    for ($i = 0; $i < $maxDepth; ++$i) {
                        $jmsContext->increaseDepth();
                    }
                }
            }

            foreach ($context->getAttributes() as $key => $value) {
                $jmsContext->attributes->set($key, $value);
            }

            if (null !== $context->getVersion()) {
                $jmsContext->setVersion($context->getVersion());
            }
            $groups = $context->getGroups();
            if (!empty($groups)) {
                $jmsContext->setGroups($context->getGroups());
            }
            if (null !== $context->getMaxDepth()) {
                $jmsContext->enableMaxDepthChecks();
            }
            if (null !== $context->getSerializeNull()) {
                $jmsContext->setSerializeNull($context->getSerializeNull());
            }
        } else {
            throw new \InvalidArgumentException('Invalid context object.');
        }

        return $jmsContext;
    }
}
