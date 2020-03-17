<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Version;

use Symfony\Component\HttpFoundation\Request;

/**
 * @author Ener-Getick <egetick@gmail.com>
 *
 * @final since 2.8
 */
class ChainVersionResolver implements VersionResolverInterface
{
    private $resolvers = [];

    /**
     * @var VersionResolverInterface[]
     */
    public function __construct(array $resolvers)
    {
        foreach ($resolvers as $resolver) {
            $this->addResolver($resolver);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Request $request)
    {
        foreach ($this->resolvers as $resolver) {
            $version = $resolver->resolve($request);

            if (null !== $version && !is_string($version)) {
                @trigger_error(sprintf('Not returning a string or null from %s::resolve() when implementing the %s is deprecated since FOSRestBundle 2.8.', get_class($resolver), VersionResolverInterface::class), E_USER_DEPRECATED);
            }

            if (null !== $version && false !== $version) {
                return $version;
            }
        }

        return null;
    }

    public function addResolver(VersionResolverInterface $resolver)
    {
        $this->resolvers[] = $resolver;
    }
}
