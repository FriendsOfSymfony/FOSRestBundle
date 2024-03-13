<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Functional\Bundle\TestBundle\Security;

use FOS\RestBundle\Tests\Functional\Bundle\TestBundle\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class ApiTokenAuthenticator extends AbstractAuthenticator
{
    protected $headerName = 'x-foo';
    protected $tokenValue = 'FOOBAR';

    public function authenticate(Request $request): Passport
    {
        $credentials = $request->headers->get($this->headerName);

        if (!$credentials || $credentials !== $this->tokenValue) {
            throw new BadCredentialsException();
        }

        $userBadge = new UserBadge($this->tokenValue, function (): \FOS\RestBundle\Tests\Functional\Bundle\TestBundle\Entity\User {
            $user = new User();
            $user->username = 'foo';
            $user->roles[] = 'ROLE_API';

            return $user;
        });

        return new SelfValidatingPassport($userBadge);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(null, 401);
    }

    public function supports(Request $request): ?bool
    {
        if (!$request->headers->has($this->headerName)) {
            return false;
        }

        return true;
    }
}
