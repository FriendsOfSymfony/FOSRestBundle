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

use FOS\RestBundle\Context\Context;
use JMS\Serializer\Context as JMSContext;
use JMS\Serializer\DeserializationContext as JMSDeserializationContext;
use JMS\Serializer\SerializationContext as JMSSerializationContext;

/**
 * BC FOSRestBundle < 2.0.
 *
 * @author Ener-Getick <egetick@gmail.com>
 *
 * @internal
 *
 * @todo Remove this in 2.0
 */
final class JMSContextAdapter
{
    public static function convertSerializationContext(Context $context)
    {
        $newContext = JMSSerializationContext::create();
        self::fillContext($context, $newContext);

        return $newContext;
    }

    public static function convertDeserializationContext(Context $context)
    {
        $newContext = JMSDeserializationContext::create();
        if (null !== $context->getMaxDepth()) {
            for ($i = 0; $i < $context->getMaxDepth(); ++$i) {
                $newContext->increaseDepth();
            }
        }

        self::fillContext($context, $newContext);

        return $newContext;
    }

    /**
     * Fill a jms context.
     *
     * @param Context    $context
     * @param JMSContext $newContext
     *
     * @return JMSContext
     */
    private static function fillContext(Context $context, JMSContext $newContext)
    {
        foreach ($context->getAttributes() as $key => $value) {
            $newContext->attributes->set($key, $value);
        }

        if (null !== $context->getVersion()) {
            $newContext->setVersion($context->getVersion());
        }
        $groups = $context->getGroups();
        if (!empty($groups)) {
            $newContext->setGroups($context->getGroups());
        }
        if (null !== $context->getMaxDepth()) {
            $newContext->enableMaxDepthChecks();
        }
        if (null !== $context->getSerializeNull()) {
            $newContext->setSerializeNull($context->getSerializeNull());
        }

        return $newContext;
    }
}
