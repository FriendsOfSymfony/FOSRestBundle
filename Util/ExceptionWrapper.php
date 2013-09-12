<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Util;

/**
 * Wraps an exception into the FOSRest exception format
 */
class ExceptionWrapper
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $message;

    /**
     * @var mixed
     */
    private $errors;

    /**
     * @param array $data
     */
    public function __construct($data)
    {
        $this->code = $data['status_code'];
        $this->message = $data['message'];

        if (isset($data['errors'])) {
            $this->errors = $data['errors'];
        }
    }
}
