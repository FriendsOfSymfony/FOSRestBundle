<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Functional;

class BasicAuthTest extends AbstractAuthenticatorTestCase
{
    protected static function getTestCase(): string
    {
        return 'BasicAuth';
    }

    protected function sendRequestContainingInvalidCredentials(string $path): void
    {
        self::$client->request('POST', $path, [], [], [
            'PHP_AUTH_USER' => 'restapi',
            'PHP_AUTH_PW' => 'wrongpw',
        ]);
    }

    protected function sendRequestContainingValidCredentials(string $path): void
    {
        self::$client->request('POST', $path, [], [], [
            'PHP_AUTH_USER' => 'restapi',
            'PHP_AUTH_PW' => 'secretpw',
        ]);
    }
}
