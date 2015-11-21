<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\View;

use FOS\RestBundle\Util\LegacyCodesHelper;

/**
 * Url based redirect implementation.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author Lukas K. Smith <smith@pooteeweet.org>
 */
class RedirectView
{
    /**
     * Convenience method to allow for a fluent interface.
     *
     * @param string $url
     * @param int    $statusCode
     * @param array  $headers
     *
     * @return View
     *
     * @deprecated To be removed in FOSRestBundle 2.0.0. Use View::createRedirect instead.
     */
    public static function create($url, $statusCode = null, array $headers = array())
    {
        if ($statusCode === null) {
            $statusCode = LegacyCodesHelper::get('HTTP_FOUND');
        }

        return View::createRedirect($url, $statusCode, $headers);
    }
}
