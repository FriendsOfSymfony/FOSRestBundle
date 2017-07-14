<?php

namespace FOS\RestBundle\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\EventListener\ExceptionListener as SymfonyHttpKernelExceptionListener;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;

class HttpKernelExceptionListener extends SymfonyHttpKernelExceptionListener
{
    /**
     * {@inheritdoc}
     */
    protected function duplicateRequest(\Exception $exception, Request $request)
    {
        $attributes = array(
            '_controller' => $this->controller,
            'exception' => $exception,
            'logger' => $this->logger instanceof DebugLoggerInterface ? $this->logger : null,
        );
        $request = $request->duplicate(null, null, $attributes);
        $request->setMethod('GET');

        return $request;
    }
}
