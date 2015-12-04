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

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides encoders through the Symfony2 DIC.
 *
 * @author Igor Wiedler <igor@wiedler.ch>
 */
class ContainerDecoderProvider implements DecoderProviderInterface, ContainerAwareInterface
{
    private $decoders;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Constructor.
     *
     * @param array $decoders List of key (format) value (service ids) of decoders
     */
    public function __construct(array $decoders)
    {
        $this->decoders = $decoders;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($format)
    {
        return isset($this->decoders[$format]);
    }

    /**
     * Sets the Container associated with this Controller.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getDecoder($format)
    {
        if (!$this->supports($format)) {
            throw new \InvalidArgumentException(
                sprintf("Format '%s' is not supported by ContainerDecoderProvider.", $format)
            );
        }

        return $this->container->get($this->decoders[$format]);
    }
}
