<?php

/*
 * This file is part of the FOSRest package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Util;

interface MediaTypeMapperInterface
{
    /**
     * @param array $controllerMap
     * @param string $mediaType
     *
     * @return null|string
     */
    public function map(array $controllerMap, $mediaType);

    /**
     * @param array $controllerMap
     *
     * @return null|string
     */
    public function fallback(array $controllerMap);
}
