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

use FOS\RestBundle\Controller\ArgumentResolver\RequestBodyValueResolver;
use Symfony\Component\HttpKernel\Attribute\ValueResolver;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

if (class_exists(ValueResolver::class)) {
    /**
     * Compat value resolver for Symfony 6.3 and newer.
     *
     * @internal
     */
    abstract class CompatMapRequestBody extends ValueResolver {}
} else {
    /**
     * Compat value resolver for Symfony 6.2 and older.
     *
     * @internal
     */
    abstract class CompatMapRequestBody
    {
        public function __construct(string $resolver)
        {
            // No-op'd constructor because the ValueResolver does not exist on this Symfony version
        }
    }
}

#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class MapRequestBody extends CompatMapRequestBody
{
    /**
     * @var ArgumentMetadata|null
     */
    public $metadata = null;

    /**
     * @var array<string, mixed>
     */
    public $deserializationContext;

    /**
     * @var bool
     */
    public $validate;

    /**
     * @var array<string, mixed>
     */
    public $validator;

    /**
     * @param array<string, mixed> $deserializationContext
     * @param array<string, mixed> $validator
     */
    public function __construct(
        array $deserializationContext = [],
        bool $validate = false,
        array $validator = [],
        string $resolver = RequestBodyValueResolver::class,
    ) {
        $this->deserializationContext = $deserializationContext;
        $this->validate = $validate;
        $this->validator = $validator;

        parent::__construct($resolver);
    }
}
