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
 * @author Ener-Getick <egetick@gmail.com>
 *
 * @internal do not use this trait or its functions in your code.
 */
trait ClassMapHandlerTrait
{
    /**
     * Resolves the value corresponding to a class from an array.
     *
     * @param string $class
     * @param array  $map
     *
     * @return mixed|false if not found
     */
    public function resolveValue($class, array $map)
    {
        foreach ($map as $mapClass => $value) {
            if (!$value) {
                continue;
            }

            if ($class === $mapClass || is_subclass_of($class, $mapClass)) {
                return $value;
            }
        }

        return false;
    }
}
