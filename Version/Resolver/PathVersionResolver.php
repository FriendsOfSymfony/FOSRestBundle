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

class PathVersionResolver implements VersionResolverInterface
{
    /**
     * @var string
     */
    private $regex;

    /**
     * Constructor.
     *
     * If your version is in path like so /api/{version}/action the following regex could be used:
     * /^\/api\/(?P<version>v?[0-9\.]+)\//
     *
     * @param string $regex regex containing a group which will result in matches containing 'version' key
     */
    public function __construct($regex)
    {
        $this->regex = $regex;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Request $request)
    {
        $path = $request->getPathInfo();

        if (false === preg_match($this->regex, $path, $matches)) {
            return false;
        }

        return isset($matches['version']) ? $matches['version'] : false;
    }
}
