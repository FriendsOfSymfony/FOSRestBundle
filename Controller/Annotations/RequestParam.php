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

/**
 * Represents a parameter that must be present in POST data.
 *
 * @Annotation
 * @NamedArgumentConstructor
 * @Target("METHOD")
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author Boris Guéry    <guery.b@gmail.com>
 */
#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_METHOD)]
class RequestParam extends AbstractScalarParam
{
    /** @var bool */
    public $strict = true;

    /**
     * @param mixed $requirements
     * @param mixed $default
     */
    public function __construct(
        string $name = '',
        ?string $key = null,
        $requirements = null,
        $default = null,
        string $description = '',
        array $incompatibles = [],
        bool $strict = true,
        bool $map = false,
        bool $nullable = false,
        bool $allowBlank = true
    ) {
        $this->name = $name;
        $this->key = $key;
        $this->requirements = $requirements;
        $this->default = $default;
        $this->description = $description;
        $this->incompatibles = $incompatibles;
        $this->strict = $strict;
        $this->map = $map;
        $this->nullable = $nullable;
        $this->allowBlank = $allowBlank;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue(Request $request, $default = null)
    {
        return $request->request->all()[$this->getKey()] ?? $default;
    }
}
