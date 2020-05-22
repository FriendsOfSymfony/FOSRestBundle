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

class CustomGuardAuthenticatorTest extends AbstractAuthenticatorTestCase
{
    protected static function getTestCase(): string
    {
        return 'CustomGuardAuthenticator';
    }

    protected function sendRequestContainingInvalidCredentials(string $path): void
    {
        self::$client->request('POST', $path, [], [], ['HTTP_X-FOO' => 'BAR', 'CONTENT_TYPE' => 'application/json']);
    }

    protected function sendRequestContainingValidCredentials(string $path): void
    {
        self::$client->request('POST', $path, [], [], ['HTTP_X-FOO' => 'FOOBAR', 'CONTENT_TYPE' => 'application/json']);
    }
}
