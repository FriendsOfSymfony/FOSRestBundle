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
 *
 * @final since 2.8
 */
class SymfonySerializerAdapter implements Serializer
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize($data, $format, Context $context)
    {
        $newContext = $this->convertContext($context);

        return $this->serializer->serialize($data, $format, $newContext);
    }

    /**
     * {@inheritdoc}
     */
    public function deserialize($data, $type, $format, Context $context)
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
        $newContext['version'] = $context->getVersion();
        $newContext['maxDepth'] = $context->getMaxDepth(false);
        $newContext['enable_max_depth'] = $context->isMaxDepthEnabled();
        $newContext['skip_null_values'] = !$context->getSerializeNull();

        return $newContext;
    }
}
