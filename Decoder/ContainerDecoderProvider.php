<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Decoder;

use Psr\Container\ContainerInterface;

/**
 * Provides encoders through the Symfony DIC.
 *
 * @author Igor Wiedler <igor@wiedler.ch>
 */
final class ContainerDecoderProvider implements DecoderProviderInterface
{
    private $container;
    private $decoders;

    /**
     * @param array<string,string> $decoders List of key (format) value (service ids) of decoders
     */
    public function __construct(ContainerInterface $container, array $decoders)
    {
        $this->container = $container;
        $this->decoders = $decoders;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $format): bool
    {
        return isset($this->decoders[$format]);
    }

    /**
     * {@inheritdoc}
     */
    public function getDecoder(string $format): DecoderInterface
    {
        if (!$this->supports($format)) {
            throw new \InvalidArgumentException(sprintf("Format '%s' is not supported by ContainerDecoderProvider.", $format));
        }

        return $this->container->get($this->decoders[$format]);
    }
}
