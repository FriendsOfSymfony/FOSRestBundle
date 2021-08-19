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
 * Represents a parameter that must be present in GET data.
 *
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"CLASS", "METHOD"})
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class QueryParam extends AbstractScalarParam
{
    /**
     * @param mixed $requirements
     * @param mixed $default
     */
    public function __construct(
        string $name = '',
        ?string $key = null,
        $requirements = null,
        $default = null,
        array $incompatibles = [],
        string $description = '',
        bool $strict = false,
        bool $map = false,
        bool $nullable = false,
        bool $allowBlank = true
    ) {
        $this->name = $name;
        $this->key = $key;
        $this->requirements = $requirements;
        $this->default = $default;
        $this->incompatibles = $incompatibles;
        $this->description = $description;
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
        return $request->query->all()[$this->getKey()] ?? $default;
    }
}
