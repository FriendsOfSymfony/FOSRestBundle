<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Serializer\Normalizer;

use FOS\RestBundle\Util\ClassMapHandlerTrait;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Ener-Getick <egetick@gmail.com>
 *
 * @internal do not use this class in your code.
 */
class AbstractExceptionNormalizer
{
    use ClassMapHandlerTrait;

    private $messagesMap;
    private $debug;

    /**
     * @param array $messagesMap
     * @param bool  $debug
     */
    public function __construct(array $messagesMap, $debug)
    {
        $this->messagesMap = $messagesMap;
        $this->debug = $debug;
    }

    /**
     * Extracts the exception message.
     *
     * @param \Exception $exception
     * @param int|null   $statusCode
     *
     * @return string
     */
    protected function getExceptionMessage(\Exception $exception, $statusCode = null)
    {
        $showMessage = $this->resolveValue(get_class($exception), $this->messagesMap);

        if ($showMessage || $this->debug) {
            return $exception->getMessage();
        }

        return array_key_exists($statusCode, Response::$statusTexts) ? Response::$statusTexts[$statusCode] : 'error';
    }
}
