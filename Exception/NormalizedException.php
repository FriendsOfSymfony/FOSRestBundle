<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Exception;

use Symfony\Component\HttpFoundation\Response;

class NormalizedException
{
    private $normalizedData;
    private $statusCode;
    private $headers;

    /**
     * @param int          $statusCode
     * @param array|scalar $normalizedData
     * @param array        $headers
     */
    public function __construct($normalizedData, $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR, array $headers = [])
    {
        $this->normalizedData = $normalizedData;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    /**
     * Returns the Exception normalized.
     *
     * @return array|scalar
     */
    public function getNormalizedData()
    {
        return $this->normalizedData;
    }

    /**
     * Returns the HTTP status code corresponding to the normalized Exception.
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Returns the headers to include in the response.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }
}
