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
class HeaderVersionResolver implements VersionResolverInterface
{
    /**
     * @var string
     */
    private $headerName;

    /**
     * @param string $headerName
     */
    public function __construct($headerName)
    {
        $this->headerName = $headerName;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Request $request)
    {
        if (!$request->headers->has($this->headerName)) {
            return false;
        }

        $header = $request->headers->get($this->headerName);

        return is_scalar($header) ? $header : strval($header);
    }
}
