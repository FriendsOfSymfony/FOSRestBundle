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

use Symfony\Component\DependencyInjection\ContainerAware;

use FOS\Rest\Decoder\DecoderProviderInterface;

/**
 * Provides encoders through the Symfony2 DIC
 *
 * @author Igor Wiedler <igor@wiedler.ch>
 */
class ContainerDecoderProvider extends ContainerAware implements DecoderProviderInterface
{
    /**
     * @var array
     */
    private $decoders;

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
     * @param string $format format
     *
     * @return boolean
     */
    public function supports($format)
    {
        return isset($this->decoders[$format]);
    }

    /**
     * @param string $format format
     *
     * @throws \InvalidArgumentException
     * @return FOS\Rest\Decoder\DecoderInterface
     */
    public function getDecoder($format)
    {
        if (!$this->supports($format)) {
            throw new \InvalidArgumentException(sprintf("Format '%s' is not supported by ContainerDecoderProvider.", $format));
        }

        return $this->container->get($this->decoders[$format]);
    }
}
