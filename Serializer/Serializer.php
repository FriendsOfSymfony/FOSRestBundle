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
    public const FOS_BUNDLE_SERIALIZATION_CONTEXT = 'fos_bundle_serialization';

    /**
     * @return string
     */
    public function serialize($data, string $format, Context $context);

    public function deserialize(string $data, string $type, string $format, Context $context);
}
