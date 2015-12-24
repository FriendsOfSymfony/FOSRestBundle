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

/**
 * {@inheritdoc}
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
class ArrayContextAdapter implements SerializationContextAdapterInterface, DeserializationContextAdapterInterface
{
    /**
     * {@inheritdoc}
     */
    public function convertSerializationContext(ContextInterface $context)
    {
        if (!$this->supportsSerialization($context)) {
            throw new \LogicException(sprintf('%s can\'t convert this serialization context.', get_class($this)));
        }

        $newContext = $this->convertContext($context);
        if ($context instanceof SerializeNullContextInterface) {
            $newContext['serializeNull'] = $context->getSerializeNull();
        }

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

        return $this->convertContext($context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsSerialization(ContextInterface $context)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDeserialization(ContextInterface $context)
    {
        return true;
    }

    /**
     * @param ContextInterface $context
     */
    private function convertContext(ContextInterface $context)
    {
        $newContext = [];
        foreach ($context->getAttributes() as $key => $value) {
            $newContext[$key] = $value;
        }

        if ($context instanceof GroupableContextInterface) {
            $newContext['groups'] = $context->getGroups();
        }
        if ($context instanceof VersionableContextInterface) {
            $newContext['version'] = $context->getVersion();
        }
        if ($context instanceof MaxDepthContextInterface) {
            $newContext['maxDepth'] = $context->getMaxDepth();
        }

        return $newContext;
    }
}
