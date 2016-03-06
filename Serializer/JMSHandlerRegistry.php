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
 * @internal do not depend on this class directly.
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
    public function registerSubscribingHandler(SubscribingHandlerInterface $handler)
    {
        return $this->registry->registerSubscribingHandler($handler);
    }

    /**
     * {@inheritdoc}
     */
    public function registerHandler($direction, $typeName, $format, $handler)
    {
        return $this->registry->registerHandler($direction, $typeName, $format, $handler);
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
        } while ($typeName = get_parent_class($typeName));
    }
}
