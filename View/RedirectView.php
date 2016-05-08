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

@trigger_error(sprintf('The %s\RedirectView class is deprecated since version 1.8 and will be removed in 2.0. Use %s\View::createRedirect() instead.', __NAMESPACE__, __NAMESPACE__), E_USER_DEPRECATED);

use FOS\RestBundle\Util\Codes;

/**
 * Url based redirect implementation.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author Lukas K. Smith <smith@pooteeweet.org>
 *
 * @deprecated since 1.8, to be removed in 2.0. Use View::createRedirect() instead.
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
    public static function create($url, $statusCode = Codes::HTTP_FOUND, array $headers = array())
    {
        return View::createRedirect($url, $statusCode, $headers);
    }
}
