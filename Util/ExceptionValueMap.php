<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Util;

/**
 * Stores map of values mapped to exception class
 * Resolves value by exception.
 *
 * @author Mikhail Shamin <munk13@gmail.com>
 *
 * @internal
 */
class ExceptionValueMap
{
    /**
     * Map of values mapped to exception class
     * key => exception class
     * value => value associated with exception.
     */
    private $map;

    /**
     * @param array<string,bool>|array<string,int> $map
     */
    public function __construct(array $map)
    {
        $this->map = $map;
    }

    /**
     * @return bool|int|null null if not found
     */
    public function resolveFromClassName(string $className)
    {
        foreach ($this->map as $mapClass => $value) {
            if (!$value) {
                continue;
            }

            if ($className === $mapClass || is_subclass_of($className, $mapClass)) {
                return $value;
            }
        }

        return null;
    }
}
