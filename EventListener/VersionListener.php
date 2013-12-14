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

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Util\ClassUtils;
use FOS\RestBundle\Controller\Annotations\Since;
use FOS\RestBundle\Controller\Annotations\Until;
use FOS\RestBundle\Exceptions\SinceException;
use FOS\RestBundle\Exceptions\UntilException;
use FOS\RestBundle\View\ConfigurableViewHandlerInterface;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class VersionListener
{
    /** @var ViewHandlerInterface */
    private $viewHandler;

    /** @var Reader */
    private $reader;

    /** @var string */
    private $regex;

    /** @var string */
    private $version = false;

    public function getVersion()
    {
        return $this->version;
    }

    public function __construct(ViewHandlerInterface $viewHandler, Reader $reader)
    {
        $this->viewHandler = $viewHandler;
        $this->reader = $reader;
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
            $this->version = $matches["version"];

            if ($this->viewHandler instanceof ConfigurableViewHandlerInterface) {
                $this->viewHandler->setExclusionStrategyVersion($this->version);
            }
        }
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        if (!$this->version) {
            return;
        }

        if (!is_array($controller = $event->getController())) {
            return;
        }

        $method = new \ReflectionMethod(ClassUtils::getClass($controller[0]), $controller[1]);

        if (!$annotations = $this->reader->getMethodAnnotations($method)) {
            return;
        }

        foreach ($annotations as $annotation) {
            if ($annotation instanceof Since) {
                if (1 === version_compare($annotation->version, $this->version)) {
                    throw new SinceException($this->version, $annotation->version);
                }
            } elseif ($annotation instanceof Until) {
                if (1 !== version_compare($annotation->version, $this->version)) {
                    throw new UntilException($this->version, $annotation->version);
                }
            }
        }
    }
}