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

use FOS\RestBundle\FOSRestBundle;
use FOS\RestBundle\Version\VersionResolverInterface;
use FOS\RestBundle\View\ConfigurableViewHandlerInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * @internal
 */
class VersionListener
{
    private $viewHandler;
    private $regex;
    private $version = false;
    private $versionResolver;
    private $defaultVersion;

    public function __construct(ViewHandlerInterface $viewHandler, VersionResolverInterface $versionResolver = null, $defaultVersion = null)
    {
        $this->viewHandler = $viewHandler;
        $this->versionResolver = $versionResolver;
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

        if (!$request->attributes->get(FOSRestBundle::ZONE_ATTRIBUTE, true)) {
            return;
        }

        if (null !== $this->regex) {
            $mediaType = $request->attributes->get('media_type');
            if (1 === preg_match($this->regex, $mediaType, $matches)) {
                $this->version = $matches['version'];
                $request->attributes->set('version', $this->version);

                if ($this->viewHandler instanceof ConfigurableViewHandlerInterface) {
                    $this->viewHandler->setExclusionStrategyVersion($this->version);
                }
            }
        } elseif (null !== $this->versionResolver) {
            $this->version = $this->versionResolver->resolve($request);
            if (false === $this->version && null !== $this->defaultVersion) {
                $this->version = $this->defaultVersion;
            }

            if (false !== $this->version) {
                $request->attributes->set('version', $this->version);

                if ($this->viewHandler instanceof ConfigurableViewHandlerInterface) {
                    $this->viewHandler->setExclusionStrategyVersion($this->version);
                }
            }
        }
    }
}
