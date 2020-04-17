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
 * @internal since 2.8
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
     * Resolves the value corresponding to an exception object.
     *
     * @return bool|int|false Value found or false is not found
     */
    public function resolveException(\Exception $exception)
    {
        return $this->resolveThrowable($exception);
    }

    /**
     * Resolves the value corresponding to an exception object.
     *
     * @return bool|int|false Value found or false is not found
     *
     * @internal since 2.8
     */
    public function resolveThrowable(\Throwable $exception)
    {
        return $this->doResolveClass(get_class($exception));
    }

    /**
     * @internal
     */
    public function resolveFromClassName(string $className)
    {
        return $this->doResolveClass($className);
    }

    /**
     * @return bool|int|false if not found
     */
    private function doResolveClass(string $class)
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
}
