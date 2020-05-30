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
use Symfony\Component\ErrorHandler\Exception\FlattenException;

/**
 * Search in the class parents to find an adapted handler.
 *
 * @author Ener-Getick <egetick@gmail.com>
 *
 * @internal do not depend on this class directly
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
        $first = true;
        do {
            $handler = $this->registry->getHandler($direction, $typeName, $format);
            if (null !== $handler) {
                if (!$first && FlattenException::class !== $typeName) {
                    @trigger_error(sprintf('The behavior of %s is deprecated since FOSRestBundle 2.8. In version 3.0, it will only force JMS Serializer to use the %s handler when possible. You should not rely on it for the parent type "%s".', __CLASS__, FlattenException::class, $typeName), E_USER_DEPRECATED);
                }

                return $handler;
            }

            $first = false;
        } while ($typeName = get_parent_class($typeName));
    }
}
