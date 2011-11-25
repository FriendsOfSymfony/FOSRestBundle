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

class ExceptionWrapper
{
    private $status;
    private $statusCode;
    private $statusText;
    private $currentContent;
    private $message;

    public function __construct($data)
    {
        $this->status = $data['status'];
        $this->statusCode = $data['status_code'];
        $this->statusText = $data['status_text'];
        $this->currentContent = $data['currentContent'];
        $this->message = $data['message'];
    }
}
