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
final class HeaderVersionResolver implements VersionResolverInterface
{
    private $headerName;

    public function __construct(string $headerName)
    {
        $this->headerName = $headerName;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Request $request): ?string
    {
        if (!$request->headers->has($this->headerName)) {
            return null;
        }

        return (string) $request->headers->get($this->headerName);
    }
}
