<?php

namespace FOS\RestBundle\Serializer;

use Symfony\Component\Serializer\Serializer as BaseSerializer,
    Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\DependencyInjection\ContainerAwareInterface;

/*
 * This file is part of the FOSRestBundle
 *
 * (c) Lukas Kahwe Smith <smith@pooteeweet.org>
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 * (c) Bulat Shakirzyanov <mallluhuct@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * A service container enabled Serializer that can lazy load normalizers and encoders
 *
 * @author Lukas K. Smith <smith@pooteeweet.org>
 */
class Serializer extends BaseSerializer implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array key format, value a service id of an EncoderInterface instance
     */
    private $encoderFormatMap;

    /**
     * @var array key class name, value a service id of an NormalizerInterface instance
     */
    private $normalizerClassMap;

    /**
     * @var array list of service id of an NormalizerInterface instance
     */
    private $defaultNormalizers;

    /**
     * Set the array maps to enable lazy loading of normalizers and encoders
     *
     * @param array $encoderFormatMap The key is the class name, the value the name of the service
     * @param array $normalizerClassMap The key is the class name, the value the name of the service
     * @param array $defaultNormalizers A list of service id of an NormalizerInterface instance
     */
    public function __construct(array $encoderFormatMap = array(), array $normalizerClassMap = array(), array $defaultNormalizers = array())
    {
        $this->encoderFormatMap = $encoderFormatMap;
        $this->normalizerClassMap = $normalizerClassMap;
        $this->defaultNormalizers = $defaultNormalizers;
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
    public function normalizeObject($object, $format = null)
    {
        try {
            return parent::normalizeObject($object, $format);
        } catch (\Exception $e) {
            $class = get_class($object);
            if (!$this->lazyLoadNormalizer($class)) {
                throw $e;
            }
        }

        return parent::normalizeObject($object, $format);
    }

    /**
     * {@inheritdoc}
     */
    public function denormalizeObject($data, $class, $format = null)
    {
        try {
            return parent::denormalizeObject($data, $class, $format);
        } catch (\Exception $e) {
            if (!$this->lazyLoadNormalizer($class)) {
                throw $e;
            }
        }

        return parent::denormalizeObject($data, $class, $format);
    }

    /**
     * Lazy load a normalizer for the given class, as well as ensure that
     * the default normalizers are loaded
     *
     * @param string $class A fully qualified class name
     *
     * @return Boolean If the normalizer was successfully lazy loaded
     */
    private function lazyLoadNormalizer($class)
    {
        $normalizer_loaded = false;
        $default_loaded = count($this->getNormalizers());

        if (isset($this->normalizerClassMap[$class])
            && $this->container->has($this->normalizerClassMap[$class])
        ) {
            $this->addNormalizer($this->container->get($this->normalizerClassMap[$class]));

            $normalizer_loaded = true;
        }

        if (!$default_loaded && !empty($this->defaultNormalizers)) {
            foreach ($this->defaultNormalizers as $normalizer) {
                $this->addNormalizer($this->container->get($normalizer));
            }

            $normalizer_loaded = true;
        }

        return $normalizer_loaded;
    }

    /**
     * {@inheritdoc}
     */
    public function encode($data, $format)
    {
        $this->lazyLoadEncoder($format);

        return parent::encode($data, $format);
    }

    /**
     * {@inheritdoc}
     */
    public function decode($data, $format)
    {
        $this->lazyLoadEncoder($format);

        return parent::decode($data, $format);
    }

    /**
     * {@inheritdoc}
     */
    public function getEncoder($format)
    {
        $this->lazyLoadEncoder($format);

        return parent::getEncoder($format);
    }

    /**
     * Lazy load an encoder for the given format
     *
     * @param string $format format name
     *
     * @return Boolean If the encoder was successfully lazy loaded
     */
    private function lazyLoadEncoder($format)
    {
        if (!$this->hasEncoder($format)
            && isset($this->encoderFormatMap[$format])
            && $this->container->has($this->encoderFormatMap[$format])
        ) {
            $this->setEncoder($format, $this->container->get($this->encoderFormatMap[$format]));

            return true;
        }

        return false;
    }
}
