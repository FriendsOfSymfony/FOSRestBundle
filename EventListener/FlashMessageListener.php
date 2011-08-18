<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace FOS\RestBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent,
    Symfony\Component\HttpFoundation\Session,
    Symfony\Component\HttpFoundation\Cookie;

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
    * Moves flash messages from the session to a cookie inside a Response Kernel listener
    *
    * @param EventInterface $event
    */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $flashes = $this->session->getFlashes();
        if (empty($flashes)) {
            return;
        }

        $this->session->clearFlashes();

        $response = $event->getResponse();

        if ($response->headers->hasCookie($this->options['name'])) {
            $cookie = $response->headers->getCookie($this->options['name']);
            $rawCookie = $cookie->getValue();
            $flashes = array_merge($flashes, explode(self::COOKIE_DELIMITER, base64_decode($rawCookie)));
        }

        $cookie = new Cookie(
            $this->options['name'],
            base64_encode(implode(self::COOKIE_DELIMITER, $flashes)),
            0,
            $this->options['path'],
            $this->options['domain'],
            $this->options['secure'],
            $this->options['httpOnly']
        );

        $response->headers->setCookie($cookie);
    }
}
