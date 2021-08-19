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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;

/**
 * Represents a file that must be present.
 *
 * @Annotation
 * @NamedArgumentConstructor
 * @Target("METHOD")
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_METHOD)]
class FileParam extends AbstractParam
{
    /** @var bool */
    public $strict = true;

    /** @var mixed */
    public $requirements = null;

    /** @var bool */
    public $image = false;

    /** @var bool */
    public $map = false;

    /**
     * @param mixed $requirements
     * @param mixed $default
     */
    public function __construct(
        string $name = '',
        bool $strict = true,
        $requirements = null,
        bool $image = false,
        bool $map = false,
        ?string $key = null,
        $default = null,
        string $description = '',
        bool $nullable = false
    ) {
        $this->strict = $strict;
        $this->requirements = $requirements;
        $this->image = $image;
        $this->map = $map;
        $this->name = $name;
        $this->key = $key;
        $this->default = $default;
        $this->description = $description;
        $this->nullable = $nullable;
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraints()
    {
        $constraints = parent::getConstraints();
        if ($this->requirements instanceof Constraint) {
            $constraints[] = $this->requirements;
        }

        $options = is_array($this->requirements) ? $this->requirements : [];
        if ($this->image) {
            $constraints[] = new Image($options);
        } else {
            $constraints[] = new File($options);
        }

        // If the user wants to map the value
        if ($this->map) {
            $constraints = [
                new All(['constraints' => $constraints]),
            ];
        }

        return $constraints;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue(Request $request, $default = null)
    {
        return $request->files->get($this->getKey(), $default);
    }
}
