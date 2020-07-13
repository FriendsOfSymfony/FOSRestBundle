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

use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\Handler\HandlerRegistryInterface;

/**
 * Search in the class parents to find an adapted handler.
 *
 * @author Ener-Getick <egetick@gmail.com>
 *
 * @internal do not depend on this class directly
 *
 * @deprecated since FOSRestBundle 3.1, use the option `fos_rest.serializer.disable_jms_handlers` to avoid relying on it.
 */
class JMSHandlerRegistry implements HandlerRegistryInterface
{
    private $registry;

    public function __construct(HandlerRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function registerSubscribingHandler(SubscribingHandlerInterface $handler): void
    {
        $this->registry->registerSubscribingHandler($handler);
    }

    /**
     * {@inheritdoc}
     */
    public function registerHandler($direction, $typeName, $format, $handler): void
    {
        $this->registry->registerHandler($direction, $typeName, $format, $handler);
    }

    /**
     * {@inheritdoc}
     */
    public function getHandler($direction, $typeName, $format)
    {
        do {
            $handler = $this->registry->getHandler($direction, $typeName, $format);
            if (null !== $handler) {
                return $handler;
            }

            @trigger_error(sprintf('Relying on the custom registry %s to inherit the JMS handler of type `%s` is deprecated since FOSRestBundle 3.1. It will be removed in version 4.0. Use the option `fos_rest.serializer.disable_custom_jms_registry` to disable it.', __CLASS__, $typeName), E_USER_DEPRECATED);
        } while ($typeName = get_parent_class($typeName));
    }
}
