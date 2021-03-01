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
    public function serialize($data, string $format, Context $context): string
    {
        $newContext = $this->convertContext($context);

        return $this->serializer->serialize($data, $format, $newContext);
    }

    /**
     * {@inheritdoc}
     */
    public function deserialize(string $data, string $type, string $format, Context $context)
    {
        $newContext = $this->convertContext($context);

        return $this->serializer->deserialize($data, $type, $format, $newContext);
    }

    private function convertContext(Context $context): array
    {
        $newContext = [];
        foreach ($context->getAttributes() as $key => $value) {
            $newContext[$key] = $value;
        }

        if (null !== $context->getGroups()) {
            $newContext['groups'] = $context->getGroups();
        }

        if (false === $context->hasAttribute(Serializer::FOS_BUNDLE_SERIALIZATION_CONTEXT)) {
            $newContext[Serializer::FOS_BUNDLE_SERIALIZATION_CONTEXT] = true;
        }

        $newContext['version'] = $context->getVersion();
        $newContext['enable_max_depth'] = $context->isMaxDepthEnabled();
        $newContext['skip_null_values'] = !$context->getSerializeNull();

        return $newContext;
    }
}
