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
 * @internal
 */
final class LegacyCodesHelper
{
    public static function get($code)
    {
        if (self::isLegacy()) {
            return constant('FOS\RestBundle\Util\Codes::'.$code);
        } else {
            return constant('Symfony\Component\HttpFoundation\Response::'.$code);
        }
    }

    public static function defined($code)
    {
        if (self::isLegacy()) {
            return defined('FOS\RestBundle\Util\Codes::'.$code);
        } else {
            return defined('Symfony\Component\HttpFoundation\Response::'.$code);
        }
    }

    public static function isLegacy()
    {
        return !defined('Symfony\Component\HttpFoundation\Response::HTTP_CONTINUE');
    }
}
