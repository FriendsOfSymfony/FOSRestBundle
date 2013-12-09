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

use FOS\RestBundle\View\ConfigurableViewHandlerInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class VersionListener
{
    /** @var ViewHandlerInterface */
    private $viewHandler;

    /** @var string */
    private $regex;

    /** @var string */
    private $version = false;

    public function getVersion()
    {
        return $this->version;
    }

    public function __construct(ViewHandlerInterface $viewHandler)
    {
        $this->viewHandler = $viewHandler;
    }

    public function setRegex($regex)
    {
        $this->regex = $regex;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $mediaType = $request->attributes->get('media_type');

        if (1 === preg_match($this->regex, $mediaType, $matches)) {
            $this->version = $matches['version'];
            $request->attributes->set('version', $this->version);

            if ($this->viewHandler instanceof ConfigurableViewHandlerInterface) {
                $this->viewHandler->setExclusionStrategyVersion($this->version);
            }
        }
    }
}