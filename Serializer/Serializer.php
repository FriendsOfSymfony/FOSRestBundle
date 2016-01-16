<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Serializer;

use FOS\RestBundle\Context\Context;

/**
 * @author Christian Flothmann <christian.flothmann@xabbuh.de>
 */
interface Serializer
{
    /**
     * @param mixed  $data
     * @param string $format
     * @param mixed  $context will only support {@link Context} in 2.0
     *
     * @return string
     */
    public function serialize($data, $format, $context = null);

    /**
     * @param string $data
     * @param string $type
     * @param string $format
     * @param mixed  $context will only support {@link Context} in 2.0
     *
     * @return mixed
     */
    public function deserialize($data, $type, $format, $context = null);
}
