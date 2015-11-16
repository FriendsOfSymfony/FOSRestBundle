<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Context;

use JMS\Serializer\Context as JMSContext;
use JMS\Serializer\DeserializationContext as JMSDeserializationContext;
use JMS\Serializer\SerializationContext as JMSSerializationContext;

/**
 * @internal
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
final class LegacyJMSContextAdapter
{
    public static function convertSerializationContext($context)
    {
        if ($context instanceof JMSContext) {
            @trigger_error('JMS\Serializer\Context support in the FOSRestBundle is deprecated since version 1.7 and will be removed in 2.0. Use FOS\RestBundle\Context\Context instead.', E_USER_DEPRECATED);

            return $context;
        }

        $newContext = JMSSerializationContext::create();
        self::fillContext($context, $newContext);

        return $newContext;
    }

    public static function convertDeserializationContext($context)
    {
        if ($context instanceof JMSContext) {
            @trigger_error('JMS\Serializer\Context support in the FOSRestBundle is deprecated since version 1.7 and will be removed in 2.0. Use FOS\RestBundle\Context\Context instead.', E_USER_DEPRECATED);

            return $context;
        }

        $newContext = JMSDeserializationContext::create();
        if ($context instanceof MaxDepthContextInterface && null !== $context->getMaxDepth()) {
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
     * @param ContextInterface $context
     * @param JMSContext       $newContext
     *
     * @return JMSContext
     */
    protected function fillContext(ContextInterface $context, JMSContext $newContext)
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
