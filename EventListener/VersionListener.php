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
use FOS\RestBundle\Controller\Annotations\Since;
use FOS\RestBundle\Controller\Annotations\Until;
use FOS\RestBundle\Exceptions\SinceException;
use FOS\RestBundle\Exceptions\UntilException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use JMS\Serializer\Context;

class VersionListener
{
    /** @var Context */
    private $context;

    /** @var Reader */
    private $reader;

    /** @var string */
    private $version = false;

    public function getVersion()
    {
        return $this->version;
    }

    public function __construct(Context $context, Reader $reader) {
        $this->context = $context;
        $this->reader = $reader;
    }

    public function onKernelRequest(GetResponseEvent $event) {
        $request = $event->getRequest();

        $acceptHeader = $request->headers->get('Accept');

        if (1 === preg_match("/(v|version)=(?P<version>[0-9\.]+)/", $acceptHeader, $matches)) {
            $this->version = $matches["version"];

            if (null !== $this->context) {
                $this->context->setVersion($this->version);
            }
        }
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        if (!$this->version)
        {
            return;
        }

        if (!is_array($controller = $event->getController())) {
            return;
        }

        $method = new \ReflectionMethod($controller[0], $controller[1]);

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