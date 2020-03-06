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
final class MediaTypeVersionResolver implements VersionResolverInterface
{
    private $regex;

    public function __construct(string $regex)
    {
        $this->regex = $regex;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Request $request): ?string
    {
        if (!$request->attributes->has('media_type') || false === preg_match($this->regex, $request->attributes->get('media_type'), $matches)) {
            return null;
        }

        return isset($matches['version']) ? $matches['version'] : null;
    }
}
