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

use Symfony\Component\Form\FormInterface;

/**
 * Wraps an exception into the FOSRest exception format.
 */
class ExceptionWrapper
{
    private $code;
    private $message;
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

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return FormInterface
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }
}
