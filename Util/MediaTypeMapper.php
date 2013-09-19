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

class MediaTypeMapper implements MediaTypeMapperInterface
{
    private $regexp = '/v(\d\.\d)/';

    /**
     * @param $regexp
     */
    public function setRegexp($regexp)
    {
        $this->regexp = $regexp;
    }

    /**
     * @param array $controllerMap
     * @param string $mediaType
     *
     * @return null|string
     */
    public function map(array $controllerMap, $mediaType)
    {
        if (preg_match($this->regexp, $mediaType, $matches) && isset($controllerMap['config'][$matches[1]])) {
            return $controllerMap['config'][$matches[1]];
        }

        return null;
    }

    /**
     * @param array $controllerMap
     *
     * @return null|string
     */
    public function fallback(array $controllerMap)
    {
        if (isset($controllerMap['fallback'])
            && isset($controllerMap['config'][$controllerMap['fallback']])
        ) {
            return $controllerMap['config'][$controllerMap['fallback']];
        }

        return null;
    }
}
