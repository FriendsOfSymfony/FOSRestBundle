<?php

namespace FOS\RestBundle\Request;

use Symfony\Component\HttpFoundation\Request;

interface ContentNegotiatorInterface
{
    function getBestMediaType(Request $request, array $availableTypes);
}