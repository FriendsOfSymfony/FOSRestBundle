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
use FOS\RestBundle\Exception\InvalidOptionException;

/**
 * {@inheritdoc}
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author Boris Guéry <guery.b@gmail.com>
 * @author Ener-Getick <egetick@gmail.com>
 */
abstract class AbstractParam implements ParamInterface
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

    public function __construct(array $data = array())
    {
        if (isset($data['value'])) {
            $data['name'] = $data['value'];
            unset($data['value']);
        }

        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /** {@inheritdoc} */
    public function getName()
    {
        return $this->name;
    }

    /** {@inheritdoc} */
    public function getDefault()
    {
        return $this->default;
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
    public function getKey()
    {
        return $this->key ?: $this->name;
    }

    public function __get($name)
    {
        throw new InvalidOptionException($name, get_class($this));
    }

    public function __set($name, $value)
    {
        throw new InvalidOptionException($name, get_class($this));
    }
}
