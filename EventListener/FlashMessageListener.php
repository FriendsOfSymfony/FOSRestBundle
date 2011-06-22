<?php

namespace FOS\RestBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent,
    Symfony\Component\HttpFoundation\Session,
    Symfony\Component\HttpFoundation\Cookie;

/*
 * This file is part of the FOSRestBundle
 *
 * (c) Lukas Kahwe Smith <smith@pooteeweet.org>
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 * (c) Bulat Shakirzyanov <avalanche123>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * This listener reads all flash messages and moves them into a cookie.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class FlashMessageListener
{
    const COOKIE_DELIMITER = ':';

    /**
     * @var array
     */
    protected $options;

    /**
     * @var Session
     */
    protected $session;

    /**
     * Set a serializer instance
     *
     * @param   Session     $session A session instance
     * @param   array       $options
     */
    public function __construct(Session $session, array $options = array())
    {
        $this->session = $session;
        $this->options = $options;
    }

   /**
    * On 'core.response' moves flash messages from the session to a cookie
    *
    * @param EventInterface $event
    */
    public function onKernelRequest(FilterResponseEvent $event)
    {
        $flashes = $this->session->getFlashes();
        if (empty($flashes)) {
            return;
        }

        $this->session->clearFlashes();

        $response = $event->getResponse();
        $cookie = $response->header->getCookie($this->options['name']);

        if ($cookie) {
            $rawCookie = $cookie->getValue();
            $flashes = array_merge($flashes, explode(self::COOKIE_DELIMITER, base64_decode($rawCookie)));
        } else {
            $cookie = new Cookie(
                $this->options['name'],
                null,
                0,
                $this->options['path'],
                $this->options['domain'],
                $this->options['secure'],
                $this->options['httponly']
            );
        }

        $cookie->setValue(base64_encode(implode(self::COOKIE_DELIMITER, $flashes)));

        $response->header->setCookie($cookie);
    }
}
