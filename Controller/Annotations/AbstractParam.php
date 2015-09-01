<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Controller\Annotations;

use Symfony\Component\Validator\Constraints;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * {@inheritdoc}
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author Boris Guéry <guery.b@gmail.com>
 * @author Ener-Getick <egetick@gmail.com>
 */
abstract class AbstractParam extends ContainerAware implements ParamInterface
{
    /** @var string */
    public $name;
    /** @var string */
    public $key;
    /** @var mixed */
    public $default;
    /** @var string */
    public $description;
    /** @var bool */
    public $strict = false;
    /** @var bool */
    public $nullable = false;
    /** @var array */
    public $incompatibles = array();

    /** {@inheritdoc} */
    public function getName()
    {
        return $this->name;
    }

    /** {@inheritdoc} */
    public function getDefault()
    {
        return $this->resolve($this->default);
    }

    /** {@inheritdoc} */
    public function getDescription()
    {
        return $this->description;
    }

    /** {@inheritdoc} */
    public function getIncompatibilities()
    {
        return $this->incompatibles;
    }

    /** {@inheritdoc} */
    public function getConstraints()
    {
        $constraints = array();
        if (!$this->nullable) {
            $constraints[] = new Constraints\NotNull();
        }

        return $constraints;
    }

    /** {@inheritdoc} */
    public function isStrict()
    {
        return $this->strict;
    }

    /**
     * @return string
     */
    protected function getKey()
    {
        return $this->key ?: $this->name;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    protected function resolve($value)
    {
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = $this->resolve($val);
            }

            return $value;
        }

        if (!is_string($value)) {
            return $value;
        }

        $container = $this->container;

        $escapedValue = preg_replace_callback('/%%|%([^%\s]++)%/', function ($match) use ($container, $value) {
            // skip %%
            if (!isset($match[1])) {
                return '%%';
            }

            if (empty($container)) {
                throw new \InvalidArgumentException(
                    'This param has been not initialized correctly. '.
                    'The container for parameter resolution is missing.'
                );
            }

            $resolved = $container->getParameter($match[1]);
            if (is_string($resolved) || is_numeric($resolved)) {
                return (string) $resolved;
            }

            throw new \RuntimeException(sprintf(
                    'The container parameter "%s", used in the controller parameters '.
                    'configuration value "%s", must be a string or numeric, but it is of type %s.',
                    $match[1],
                    $value,
                    gettype($resolved)
                )
            );
        }, $value);

        return str_replace('%%', '%', $escapedValue);
    }
}
