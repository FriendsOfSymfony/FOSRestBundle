<?php

namespace FOS\RestBundle\Version\Resolver;

use FOS\RestBundle\Version\VersionResolverInterface;
use Symfony\Component\HttpFoundation\Request;

class UrlVersionResolver implements VersionResolverInterface
{
    /**
     * @var string
     */
    private $parameterName;

    /**
     * @param string $parameterName
     */
    public function __construct($parameterName)
    {
        $this->parameterName = $parameterName;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Request $request)
    {
       preg_match($this->parameterName, $request->getUri(), $matches);

       return !empty($matches) ? $matches[0] : false;
    }
}