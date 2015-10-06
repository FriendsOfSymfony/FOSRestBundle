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
    private $viewHandler;
    private $regex;
    private $version = false;
    private $defaultVersion;

    public function __construct(ViewHandlerInterface $viewHandler, $defaultVersion = null)
    {
        $this->viewHandler = $viewHandler;
        $this->defaultVersion = $defaultVersion;
    }

    /**
     * Gets the version.
     *
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Sets the regex.
     *
     * @param string $regex
     */
    public function setRegex($regex)
    {
        $this->regex = $regex;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $mediaType = $request->attributes->get('media_type');

        if (1 === preg_match($this->regex, $mediaType, $matches) || null !== $this->defaultVersion) {
            $this->version = isset($matches['version']) ? $matches['version'] : $this->defaultVersion;
            $request->attributes->set('version', $this->version);

            if ($this->viewHandler instanceof ConfigurableViewHandlerInterface) {
                $this->viewHandler->setExclusionStrategyVersion($this->version);
            }
        }
    }
}
