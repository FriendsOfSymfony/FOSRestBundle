<?php

namespace FOS\RestBundle\View;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class RequestListener
{
    protected $detectFormat;
    protected $decodeBody;
    protected $defaultFormat;

    public function __construct($detectFormat, $decodeBody, $defaultFormat)
    {
        $this->detectFormat = $detectFormat;
        $this->decodeBody = $decodeBody;
        $this->defaultFormat = $defaultFormat;
    }

    public function onCoreRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if ($this->detectFormat) {
            $this->detectFormat($request);
        }

        if ($this->decodeBody) {
            $this->detectFormat($request);
        }
    }

    protected function detectFormat($request)
    {
        // TODO enable once https://github.com/symfony/symfony/pull/565 is merged
//        $format = $request->getRequestFormat(null);
        $format = $request->get('_format');
        if (null === $format) {
            $formats = $this->splitHttpAcceptHeader($request->headers->get('Accept'));
            if (!empty($formats)) {
                $format = $request->getFormat(key($formats));
            }

            if (null === $format) {
                $format = $this->defaultFormat;
            }
        }

        $request->setRequestFormat($format);
    }

    protected function decodeBody($request)
    {
        // TODO: this is totally incomplete and untested code
        if (in_array($request->getMethod(), array('POST', 'PUT', 'DELETE'))) {
            switch ($request->getFormat($request->headers->get('Content-Type'))) {
                case 'json':
                    $post = json_decode($request->getContent());
                    break;
                case 'xml':
                    $post = simplexml_load_string($request->getContent());
                    break;
                default:
                    return;
            }

            $request->request = new ParameterBag((array)$post);
        }
    }

    /**
     * Splits an Accept-* HTTP header.
     * TODO remove once https://github.com/symfony/symfony/pull/565 is merged
     *
     * @param string $header  Header to split
     */
    private function splitHttpAcceptHeader($header)
    {
        if (!$header) {
            return array();
        }

        $values = array();
        foreach (array_filter(explode(',', $header)) as $value) {
            // Cut off any q-value that might come after a semi-colon
            if ($pos = strpos($value, ';')) {
                $q     = (float) trim(substr($value, strpos($value, '=') + 1));
                $value = trim(substr($value, 0, $pos));
            } else {
                $q = 1;
            }

            if (0 < $q) {
                $values[trim($value)] = $q;
            }
        }

        arsort($values);
        reset($values);

        return $values;
    }

}
