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

class ExceptionValueMap
{
    /**
     * Map of values mapped to exception class
     * key => exception class
     * value => value associated with exception.
     *
     * @var array
     */
    private $map;

    /**
     * @param array $map
     */
    public function __construct(array $map)
    {
        $this->map = $map;
    }

    /**
     * Resolves the value corresponding to a class from an array.
     *
     * @param string $class
     *
     * @return mixed|false if not found
     */
    public function resolveClass($class)
    {
        foreach ($this->map as $mapClass => $value) {
            if (!$value) {
                continue;
            }

            if ($class === $mapClass || is_subclass_of($class, $mapClass)) {
                return $value;
            }
        }

        return false;
    }

    /**
     * Get value by exception.
     *
     * @param \Exception $exception
     *
     * @return false|mixed
     */
    public function resolveException(\Exception $exception)
    {
        return $this->resolveClass(get_class($exception));
    }
}
