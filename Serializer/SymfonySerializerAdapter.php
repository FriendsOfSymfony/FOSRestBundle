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
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Adapter to plug the Symfony serializer into the FOSRestBundle Serializer API.
 *
 * @author Christian Flothmann <christian.flothmann@xabbuh.de>
 */
final class SymfonySerializerAdapter implements Serializer
{
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
        if (!($context instanceof Context)) {
            @trigger_error(sprintf('You must pass a FOS\RestBundle\Context\Context instance to %s since version 1.8.', __METHOD__), E_USER_DEPRECATED);
            $newContext = array();
        } else {
            $newContext = $this->convertContext($context);
            $newContext['serializeNull'] = $context->getSerializeNull();
        }

        return $this->serializer->serialize($data, $format, $newContext);
    }

    /**
     * {@inheritdoc}
     */
    public function deserialize($data, $type, $format, $context = null)
    {
        if (!($context instanceof Context)) {
            @trigger_error(sprintf('You must pass a FOS\RestBundle\Context\Context instance to %s since version 1.8.', __METHOD__), E_USER_DEPRECATED);
            $newContext = array();
        } else {
            $newContext = $this->convertContext($context);
        }

        return $this->serializer->deserialize($data, $type, $format, $newContext);
    }

    /**
     * @param Context $context
     */
    private function convertContext(Context $context)
    {
        $newContext = array();
        foreach ($context->getAttributes() as $key => $value) {
            $newContext[$key] = $value;
        }

        $newContext['groups'] = $context->getGroups();
        $newContext['version'] = $context->getVersion();
        $newContext['maxDepth'] = $context->getMaxDepth();

        return $newContext;
    }
}
