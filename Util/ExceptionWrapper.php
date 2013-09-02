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
    /**
     * @var array
     */
    private $error = array('code' => null, 'message' => null);

    public function __construct($data)
    {
        $this->error['code'] = $data['status_code'];
        $this->error['message'] = $data['message'];
        if (isset($data['errors'])) {
            $this->error['errors'] = $data['errors'];
        }
    }
}
