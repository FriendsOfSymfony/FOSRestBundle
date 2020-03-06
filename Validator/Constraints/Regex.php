<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Validator\Constraints;

use FOS\RestBundle\Util\ResolverTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraints\Regex as BaseRegex;
use Symfony\Component\Validator\Constraints\RegexValidator;

/**
 * @Annotation
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
class Regex extends BaseRegex implements ResolvableConstraintInterface
{
    use ResolverTrait;

    private $resolved;

    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return RegexValidator::class;
    }

    public function resolve(ContainerInterface $container): void
    {
        if ($this->resolved) {
            return;
        }
        $this->pattern = $this->resolveValue($container, $this->pattern);
        $this->resolved = true;
    }
}
