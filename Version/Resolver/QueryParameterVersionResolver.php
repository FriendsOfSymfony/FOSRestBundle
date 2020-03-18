<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Version\Resolver;

use FOS\RestBundle\Version\VersionResolverInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
final class QueryParameterVersionResolver implements VersionResolverInterface
{
    private $parameterName;

    public function __construct(string $parameterName)
    {
        $this->parameterName = $parameterName;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Request $request): ?string
    {
        if (!$request->query->has($this->parameterName)) {
            return null;
        }

        return (string) $request->query->get($this->parameterName);
    }
}
